<?php

namespace App\Filament\Pages;

use App\Models\Receivable;
use App\Models\Payable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class AgingReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
    protected static ?string $navigationLabel = 'Aging Piutang & Hutang';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.aging-report';

    public string $type = 'receivable';
    public string $as_of_date;
    public array $reportData = [];

    public function mount(): void
    {
        $this->as_of_date = now()->toDateString();
    }

    public function generate(): void
    {
        $asOf = \Carbon\Carbon::parse($this->as_of_date);

        $buckets = [
            'current'  => ['label' => 'Current (0-30 hari)',    'min' => 0,   'max' => 30],
            'b31_60'   => ['label' => '31-60 hari',             'min' => 31,  'max' => 60],
            'b61_90'   => ['label' => '61-90 hari',             'min' => 61,  'max' => 90],
            'b91_180'  => ['label' => '91-180 hari',            'min' => 91,  'max' => 180],
            'over180'  => ['label' => '> 180 hari',             'min' => 181, 'max' => 99999],
        ];

        $records = $this->type === 'receivable'
            ? Receivable::unpaid()->with('account')->get()
            : Payable::unpaid()->with('account')->get();

        $result = [];
        $grandTotal = array_fill_keys(array_keys($buckets), 0);
        $grandTotal['total'] = 0;

        foreach ($records as $record) {
            $sisa = $this->type === 'receivable'
                ? (float) $record->amount - (float) $record->paid_amount - (float) $record->discount
                : (float) $record->amount - (float) $record->paid_amount - (float) $record->discount;

            if ($sisa <= 0) continue;

            $days = $asOf->diffInDays($record->invoice_date, false) * -1;
            if ($days < 0) $days = 0;

            $bucket = 'over180';
            foreach ($buckets as $key => $range) {
                if ($days >= $range['min'] && $days <= $range['max']) {
                    $bucket = $key;
                    break;
                }
            }

            $name = $this->type === 'receivable' ? $record->debtor_name : $record->creditor_name;

            if (!isset($result[$name])) {
                $result[$name] = [
                    'name'    => $name,
                    'penjamin'=> $this->type === 'receivable' ? $record->penjamin : null,
                    'current' => 0, 'b31_60' => 0, 'b61_90' => 0,
                    'b91_180' => 0, 'over180' => 0, 'total' => 0,
                ];
            }

            $result[$name][$bucket] += $sisa;
            $result[$name]['total'] += $sisa;
            $grandTotal[$bucket]   += $sisa;
            $grandTotal['total']   += $sisa;
        }

        usort($result, fn($a, $b) => $b['total'] <=> $a['total']);

        $this->reportData = [
            'rows'        => array_values($result),
            'grand_total' => $grandTotal,
            'buckets'     => $buckets,
            'as_of_date'  => $asOf->translatedFormat('d F Y'),
        ];
    }
}