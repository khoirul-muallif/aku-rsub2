<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalLine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $year  = now()->year;
        $month = now()->month;

        // Total Aset
        $totalAset = Account::active()->where('type', 'asset')->posting()->get()
            ->sum(function ($acc) use ($year, $month) {
                $lines = JournalLine::where('account_id', $acc->id)
                    ->whereHas('journal', fn($q) => $q->where('period_year', $year)
                        ->where('period_month', $month)->where('status', 'posted'))
                    ->selectRaw('SUM(debit) as d, SUM(credit) as c')->first();
                return $acc->current_balance + ($lines->d ?? 0) - ($lines->c ?? 0);
            });

        // Total Pendapatan bulan ini
        $totalPendapatan = JournalLine::whereHas('account', fn($q) => $q->where('type', 'revenue'))
            ->whereHas('journal', fn($q) => $q->where('period_year', $year)
                ->where('period_month', $month)->where('status', 'posted'))
            ->sum('credit');

        // Total Beban bulan ini
        $totalBeban = JournalLine::whereHas('account', fn($q) => $q->where('type', 'expense'))
            ->whereHas('journal', fn($q) => $q->where('period_year', $year)
                ->where('period_month', $month)->where('status', 'posted'))
            ->sum('debit');

        // Laba/Rugi
        $labaRugi = $totalPendapatan - $totalBeban;

        // Jurnal draft (belum diposting)
        $jurnalDraft = Journal::where('status', 'draft')->count();

        // Total jurnal bulan ini
        $totalJurnal = Journal::where('period_year', $year)
            ->where('period_month', $month)
            ->where('status', 'posted')
            ->count();

        return [
            Stat::make('Total Aset', 'Rp ' . number_format($totalAset, 0, ',', '.'))
                ->description('Per ' . now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-building-library')
                ->color('success'),

            Stat::make('Pendapatan ' . now()->translatedFormat('F'), 'Rp ' . number_format($totalPendapatan, 0, ',', '.'))
                ->description('Bulan berjalan')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Beban ' . now()->translatedFormat('F'), 'Rp ' . number_format($totalBeban, 0, ',', '.'))
                ->description('Bulan berjalan')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make('Laba / Rugi', 'Rp ' . number_format(abs($labaRugi), 0, ',', '.'))
                ->description($labaRugi >= 0 ? 'Laba bulan ini' : 'Rugi bulan ini')
                ->descriptionIcon($labaRugi >= 0 ? 'heroicon-o-face-smile' : 'heroicon-o-face-frown')
                ->color($labaRugi >= 0 ? 'success' : 'danger'),

            Stat::make('Jurnal Draft', $jurnalDraft)
                ->description('Belum diposting')
                ->descriptionIcon('heroicon-o-clock')
                ->color($jurnalDraft > 0 ? 'warning' : 'gray'),

            Stat::make('Jurnal Bulan Ini', $totalJurnal)
                ->description('Sudah diposting')
                ->descriptionIcon('heroicon-o-document-check')
                ->color('teal'),
        ];
    }
}