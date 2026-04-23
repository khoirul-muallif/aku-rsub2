<?php

namespace App\Filament\Widgets;

use App\Models\JournalLine;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Pendapatan vs Beban';
    protected static  ?int $sort = 2;
    protected string $color = 'info';

    protected function getData(): array
    {
        $labels     = [];
        $pendapatan = [];
        $beban      = [];

        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $year  = $date->year;
            $month = $date->month;

            $labels[] = $date->translatedFormat('M Y');

            $pendapatan[] = JournalLine::whereHas('account', fn($q) => $q->where('type', 'revenue'))
                ->whereHas('journal', fn($q) => $q
                    ->where('period_year', $year)
                    ->where('period_month', $month)
                    ->where('status', 'posted'))
                ->sum('credit');

            $beban[] = JournalLine::whereHas('account', fn($q) => $q->where('type', 'expense'))
                ->whereHas('journal', fn($q) => $q
                    ->where('period_year', $year)
                    ->where('period_month', $month)
                    ->where('status', 'posted'))
                ->sum('debit');
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Pendapatan',
                    'data'            => $pendapatan,
                    'backgroundColor' => '#0d9488',
                    'borderColor'     => '#0d9488',
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Beban',
                    'data'            => $beban,
                    'backgroundColor' => '#ef4444',
                    'borderColor'     => '#ef4444',
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}