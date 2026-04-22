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

class BukuBesar extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';  // ← pakai UnitEnum
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.buku-besar';

    public ?int $period_year = null;
    public ?int $period_month = null;
    public ?int $account_id = null;
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

            Select::make('account_id')
                ->label('Akun (kosongkan untuk semua)')
                ->options(
                    Account::active()->orderBy('code')
                        ->get()->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"])
                )
                ->searchable()
                ->nullable(),
        ])->columns(3);
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