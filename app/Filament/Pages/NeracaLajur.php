<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\JournalLine;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class NeracaLajur extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;
    protected static ?string $navigationLabel = 'Neraca Lajur';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.pages.neraca-lajur';

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
        $accounts = Account::active()
            ->posting()
            ->orderBy('code')
            ->get();

        $rows = [];
        $totals = [
            'ns_debit'  => 0, 'ns_credit'  => 0,
            'lr_debit'  => 0, 'lr_credit'  => 0,
            'ner_debit' => 0, 'ner_credit' => 0,
        ];

        foreach ($accounts as $account) {
            // Ambil mutasi periode ini
            $lines = JournalLine::where('account_id', $account->id)
                ->whereHas('journal', fn($q) => $q
                    ->where('period_year', $this->period_year)
                    ->where('period_month', $this->period_month)
                    ->where('status', 'posted'))
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

            $mutasiDebit  = (float) ($lines->total_debit ?? 0);
            $mutasiKredit = (float) ($lines->total_credit ?? 0);

            // Hitung saldo neraca saldo (saldo awal + mutasi)
            $saldoAwal = (float) $account->current_balance;

            if ($account->normal_side === 'debit') {
                $nsDebit  = $saldoAwal + $mutasiDebit;
                $nsCredit = $mutasiKredit;
                if ($nsDebit > $nsCredit) {
                    $nsDebit  = $nsDebit - $nsCredit;
                    $nsCredit = 0;
                } else {
                    $nsCredit = $nsCredit - $nsDebit;
                    $nsDebit  = 0;
                }
            } else {
                $nsCredit = $saldoAwal + $mutasiKredit;
                $nsDebit  = $mutasiDebit;
                if ($nsCredit > $nsDebit) {
                    $nsCredit = $nsCredit - $nsDebit;
                    $nsDebit  = 0;
                } else {
                    $nsDebit  = $nsDebit - $nsCredit;
                    $nsCredit = 0;
                }
            }

            // Skip akun yang saldo 0
            if ($nsDebit == 0 && $nsCredit == 0) continue;

            // Klasifikasi ke Laba Rugi atau Neraca
            $lrDebit = $lrCredit = $nerDebit = $nerCredit = 0;

            if (in_array($account->type, ['revenue', 'expense'])) {
                $lrDebit  = $nsDebit;
                $lrCredit = $nsCredit;
            } else {
                $nerDebit  = $nsDebit;
                $nerCredit = $nsCredit;
            }

            $rows[] = [
                'code'       => $account->code,
                'name'       => $account->name,
                'type'       => $account->type,
                'ns_debit'   => $nsDebit,
                'ns_credit'  => $nsCredit,
                'lr_debit'   => $lrDebit,
                'lr_credit'  => $lrCredit,
                'ner_debit'  => $nerDebit,
                'ner_credit' => $nerCredit,
            ];

            $totals['ns_debit']   += $nsDebit;
            $totals['ns_credit']  += $nsCredit;
            $totals['lr_debit']   += $lrDebit;
            $totals['lr_credit']  += $lrCredit;
            $totals['ner_debit']  += $nerDebit;
            $totals['ner_credit'] += $nerCredit;
        }

        // Hitung laba/rugi untuk penyeimbang
        $labaRugi = $totals['lr_credit'] - $totals['lr_debit'];

        $this->reportData = [
            'rows'      => $rows,
            'totals'    => $totals,
            'laba_rugi' => $labaRugi,
        ];
    }
}