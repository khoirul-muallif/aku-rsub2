<?php

namespace App\Filament\Pages;

use App\Models\Account;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class BukuBesar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.buku-besar';

    public int $period_year;
    public int $period_month;
    public ?int $account_id = null;
    public array $reportData = [];

    public function mount(): void
    {
        $this->period_year  = now()->year;
        $this->period_month = now()->month;
    }

    public function generate(): void
    {
        $query = Account::active()
            ->with(['journalLines' => function ($q) {
                $q->whereHas('journal', function ($j) {
                    $j->where('period_year', $this->period_year)
                      ->where('period_month', $this->period_month)
                      ->where('status', 'posted');
                })->with('journal');
            }])
            ->orderBy('code');

        if ($this->account_id) {
            $query->where('id', $this->account_id);
        } else {
            $query->posting();
        }

        $accounts = $query->get();
        $this->reportData = [];

        foreach ($accounts as $account) {
            $totalDebit  = $account->journalLines->sum('debit');
            $totalCredit = $account->journalLines->sum('credit');

            if ($account->normal_side === 'debit') {
                $saldoAkhir = $account->current_balance + $totalDebit - $totalCredit;
            } else {
                $saldoAkhir = $account->current_balance - $totalDebit + $totalCredit;
            }

            $this->reportData[] = [
                'account'      => $account,
                'saldo_awal'   => $account->current_balance,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'saldo_akhir'  => $saldoAkhir,
                'lines'        => $account->journalLines,
            ];
        }
    }
}