<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\BankStatement;
use App\Models\JournalLine;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class RekonsiliasiBank extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;
    protected static ?string $navigationLabel = 'Rekonsiliasi Bank';
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.rekonsiliasi-bank';

    public ?int $account_id = null;
    public int $period_year;
    public int $period_month;
    public array $reportData = [];

    // Form tambah mutasi bank
    public string $tx_date = '';
    public string $tx_desc = '';
    public float $tx_debit = 0;
    public float $tx_credit = 0;
    public float $tx_balance = 0;

    public function mount(): void
    {
        $this->period_year  = now()->year;
        $this->period_month = now()->month;
        $this->tx_date      = now()->toDateString();
    }

    public function addStatement(): void
    {
        if (!$this->account_id || !$this->tx_date) return;

        BankStatement::create([
            'account_id'       => $this->account_id,
            'period_year'      => $this->period_year,
            'period_month'     => $this->period_month,
            'transaction_date' => $this->tx_date,
            'description'      => $this->tx_desc,
            'debit'            => $this->tx_debit,
            'credit'           => $this->tx_credit,
            'balance'          => $this->tx_balance,
        ]);

        // Reset form
        $this->tx_desc    = '';
        $this->tx_debit   = 0;
        $this->tx_credit  = 0;
        $this->tx_balance = 0;

        $this->generate();
    }

    public function deleteStatement(int $id): void
    {
        BankStatement::find($id)?->delete();
        $this->generate();
    }

    public function generate(): void
    {
        if (!$this->account_id) return;

        $account = Account::find($this->account_id);

        // Saldo buku dari jurnal
        $lines = JournalLine::where('account_id', $this->account_id)
            ->whereHas('journal', fn($q) => $q
                ->where('period_year', $this->period_year)
                ->where('period_month', $this->period_month)
                ->where('status', 'posted'))
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $mutasiDebit  = (float) ($lines->total_debit ?? 0);
        $mutasiKredit = (float) ($lines->total_credit ?? 0);
        $saldoAwal    = (float) $account->current_balance;

        if ($account->normal_side === 'debit') {
            $saldoBuku = $saldoAwal + $mutasiDebit - $mutasiKredit;
        } else {
            $saldoBuku = $saldoAwal + $mutasiKredit - $mutasiDebit;
        }

        // Mutasi bank
        $statements = BankStatement::where('account_id', $this->account_id)
            ->where('period_year', $this->period_year)
            ->where('period_month', $this->period_month)
            ->orderBy('transaction_date')
            ->get();

        $totalBankDebit  = $statements->sum('debit');
        $totalBankKredit = $statements->sum('credit');
        $saldoBank       = $statements->last()?->balance ?? 0;

        // Kalau tidak ada saldo akhir, hitung dari saldo awal + mutasi
        if ($saldoBank == 0 && $statements->count() > 0) {
            $saldoBank = $saldoAwal + $totalBankDebit - $totalBankKredit;
        }

        $selisih = $saldoBuku - $saldoBank;

        $this->reportData = [
            'account'           => $account,
            'saldo_awal'        => $saldoAwal,
            'mutasi_debit_buku' => $mutasiDebit,
            'mutasi_kredit_buku'=> $mutasiKredit,
            'saldo_buku'        => $saldoBuku,
            'statements'        => $statements,
            'total_bank_debit'  => $totalBankDebit,
            'total_bank_kredit' => $totalBankKredit,
            'saldo_bank'        => $saldoBank,
            'selisih'           => $selisih,
        ];
    }
}