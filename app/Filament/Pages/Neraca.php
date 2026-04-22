<?php

namespace App\Filament\Pages;

use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class Neraca extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;
    protected static ?string $navigationLabel = 'Neraca';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.neraca';

    public ?int $period_year = null;
    public ?int $period_month = null;
    public array $reportData = [];

    public function mount(): void
    {
        $this->period_year  = now()->year;
        $this->period_month = now()->month;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('period_year')
                ->label('Tahun')
                ->options(array_combine(
                    range(now()->year, now()->year - 5),
                    range(now()->year, now()->year - 5)
                ))
                ->default(now()->year)
                ->required(),

            Select::make('period_month')
                ->label('Bulan')
                ->options([
                    1  => 'Januari',   2  => 'Februari', 3  => 'Maret',
                    4  => 'April',     5  => 'Mei',       6  => 'Juni',
                    7  => 'Juli',      8  => 'Agustus',   9  => 'September',
                    10 => 'Oktober',   11 => 'November',  12 => 'Desember',
                ])
                ->default(now()->month)
                ->required(),
        ])->columns(2);
    }

    public function generate(): void
    {
        $types = ['asset', 'liability', 'equity'];
        $result = [];

        foreach ($types as $type) {
            $accounts = Account::active()
                ->where('type', $type)
                ->posting()
                ->with(['journalLines' => function ($q) {
                    $q->whereHas('journal', function ($j) {
                        $j->where('period_year', $this->period_year)
                          ->where('period_month', $this->period_month)
                          ->where('status', 'posted');
                    });
                }])
                ->orderBy('code')
                ->get();

            $total = 0;
            $list  = [];

            foreach ($accounts as $acc) {
                $debit  = $acc->journalLines->sum('debit');
                $credit = $acc->journalLines->sum('credit');

                if ($acc->normal_side === 'debit') {
                    $saldo = $acc->current_balance + $debit - $credit;
                } else {
                    $saldo = $acc->current_balance - $debit + $credit;
                }

                $total += $saldo;
                $list[] = ['account' => $acc, 'saldo' => $saldo];
            }

            $result[$type] = ['list' => $list, 'total' => $total];
        }

        // Hitung laba rugi berjalan
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
            'asset'              => $result['asset'],
            'liability'          => $result['liability'],
            'equity'             => $result['equity'],
            'laba_rugi_berjalan' => $labaRugiBerjalan,
            'total_liability_equity' => $result['liability']['total'] + $result['equity']['total'] + $labaRugiBerjalan,
        ];
    }
}