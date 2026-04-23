<?php

namespace App\Filament\Pages;

use App\Models\Account;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class LabaRugi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'Laba Rugi';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.laba-rugi';

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
        $pendapatan = Account::active()->where('type', 'revenue')->posting()
            ->with(['journalLines' => function ($q) {
                $q->whereHas('journal', function ($j) {
                    $j->where('period_year', $this->period_year)
                      ->where('period_month', $this->period_month)
                      ->where('status', 'posted');
                });
            }])->orderBy('code')->get();

        $beban = Account::active()->where('type', 'expense')->posting()
            ->with(['journalLines' => function ($q) {
                $q->whereHas('journal', function ($j) {
                    $j->where('period_year', $this->period_year)
                      ->where('period_month', $this->period_month)
                      ->where('status', 'posted');
                });
            }])->orderBy('code')->get();

        $totalPendapatan = 0;
        $pendapatanList  = [];
        foreach ($pendapatan as $acc) {
            $jumlah = $acc->journalLines->sum('credit') - $acc->journalLines->sum('debit');
            $totalPendapatan += $jumlah;
            $pendapatanList[] = ['account' => $acc, 'jumlah' => $jumlah];
        }

        $totalBeban = 0;
        $bebanList  = [];
        foreach ($beban as $acc) {
            $jumlah = $acc->journalLines->sum('debit') - $acc->journalLines->sum('credit');
            $totalBeban += $jumlah;
            $bebanList[] = ['account' => $acc, 'jumlah' => $jumlah];
        }

        $this->reportData = [
            'pendapatan'       => $pendapatanList,
            'beban'            => $bebanList,
            'total_pendapatan' => $totalPendapatan,
            'total_beban'      => $totalBeban,
            'laba_rugi'        => $totalPendapatan - $totalBeban,
        ];
    }
}