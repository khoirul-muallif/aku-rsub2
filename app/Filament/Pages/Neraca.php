<?php

namespace App\Filament\Pages;

use App\Models\Account;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class Neraca extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;
    protected static ?string $navigationLabel = 'Neraca';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.neraca';

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
        $types  = ['asset', 'liability', 'equity'];
        $result = [];

        foreach ($types as $type) {
            $accounts = Account::active()->where('type', $type)->posting()
                ->with(['journalLines' => function ($q) {
                    $q->whereHas('journal', function ($j) {
                        $j->where('period_year', $this->period_year)
                          ->where('period_month', $this->period_month)
                          ->where('status', 'posted');
                    });
                }])->orderBy('code')->get();

            $total = 0;
            $list  = [];
            foreach ($accounts as $acc) {
                $debit  = $acc->journalLines->sum('debit');
                $credit = $acc->journalLines->sum('credit');
                $saldo  = $acc->normal_side === 'debit'
                    ? $acc->current_balance + $debit - $credit
                    : $acc->current_balance - $debit + $credit;
                $total += $saldo;
                $list[] = ['account' => $acc, 'saldo' => $saldo];
            }
            $result[$type] = ['list' => $list, 'total' => $total];
        }

        $pendapatan = Account::active()->where('type', 'revenue')->posting()
            ->with(['journalLines' => function ($q) {
                $q->whereHas('journal', function ($j) {
                    $j->where('period_year', $this->period_year)
                      ->where('period_month', $this->period_month)
                      ->where('status', 'posted');
                });
            }])->get()->sum(fn($a) => $a->journalLines->sum('credit') - $a->journalLines->sum('debit'));

        $beban = Account::active()->where('type', 'expense')->posting()
            ->with(['journalLines' => function ($q) {
                $q->whereHas('journal', function ($j) {
                    $j->where('period_year', $this->period_year)
                      ->where('period_month', $this->period_month)
                      ->where('status', 'posted');
                });
            }])->get()->sum(fn($a) => $a->journalLines->sum('debit') - $a->journalLines->sum('credit'));

        $labaRugiBerjalan = $pendapatan - $beban;

        $this->reportData = [
            'asset'                  => $result['asset'],
            'liability'              => $result['liability'],
            'equity'                 => $result['equity'],
            'laba_rugi_berjalan'     => $labaRugiBerjalan,
            'total_liability_equity' => $result['liability']['total'] + $result['equity']['total'] + $labaRugiBerjalan,
        ];
    }
}