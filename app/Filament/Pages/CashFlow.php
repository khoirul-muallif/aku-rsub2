<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\JournalLine;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class CashFlow extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'Cash Flow';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.cash-flow';

    public int $period_year;
    public int $period_month;
    public array $reportData = [];

    public function mount(): void
    {
        $this->period_year  = now()->year;
        $this->period_month = now()->month;
    }

    public function generate(): void
    {
        // Akun kas & bank
        $akunKas = Account::active()
            ->where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1.1%');
            })
            ->posting()
            ->pluck('id');

        // Penerimaan kas (debit ke akun kas/bank)
        $penerimaanLines = JournalLine::whereIn('account_id', $akunKas)
            ->whereHas('journal', fn($q) => $q
                ->where('period_year', $this->period_year)
                ->where('period_month', $this->period_month)
                ->where('status', 'posted')
                ->whereIn('journal_type', ['kas', 'bank']))
            ->where('debit', '>', 0)
            ->with(['journal', 'account'])
            ->get();

        // Pengeluaran kas (kredit dari akun kas/bank)
        $pengeluaranLines = JournalLine::whereIn('account_id', $akunKas)
            ->whereHas('journal', fn($q) => $q
                ->where('period_year', $this->period_year)
                ->where('period_month', $this->period_month)
                ->where('status', 'posted')
                ->whereIn('journal_type', ['kas', 'bank']))
            ->where('credit', '>', 0)
            ->with(['journal', 'account'])
            ->get();

        $totalPenerimaan  = $penerimaanLines->sum('debit');
        $totalPengeluaran = $pengeluaranLines->sum('credit');
        $netCashFlow      = $totalPenerimaan - $totalPengeluaran;

        // Saldo awal kas (current_balance)
        $saldoAwal = Account::whereIn('id', $akunKas)->sum('current_balance');

        $this->reportData = [
            'penerimaan'       => $penerimaanLines,
            'pengeluaran'      => $pengeluaranLines,
            'total_penerimaan' => $totalPenerimaan,
            'total_pengeluaran'=> $totalPengeluaran,
            'net_cash_flow'    => $netCashFlow,
            'saldo_awal'       => $saldoAwal,
            'saldo_akhir'      => $saldoAwal + $netCashFlow,
        ];
    }
}