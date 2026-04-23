<?php

namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class NeracaExport implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        private int $year,
        private int $month
    ) {}

    public function collection(): Collection
    {
        $rows = collect();
        $rows->push(['RSU. BANYUMANIK 2', '']);
        $rows->push(['NERACA', '']);
        $rows->push(['Per : ' . \Carbon\Carbon::create($this->year, $this->month)->translatedFormat('F Y'), '']);
        $rows->push(['', '']);

        foreach (['asset' => 'ASET', 'liability' => 'KEWAJIBAN', 'equity' => 'EKUITAS'] as $type => $label) {
            $rows->push([$label, '']);
            $accounts = Account::active()->where('type', $type)->posting()
                ->with(['journalLines' => fn($q) => $q->whereHas('journal', fn($j) =>
                    $j->where('period_year', $this->year)->where('period_month', $this->month)->where('status', 'posted')
                )])->orderBy('code')->get();

            $total = 0;
            foreach ($accounts as $acc) {
                $debit  = $acc->journalLines->sum('debit');
                $credit = $acc->journalLines->sum('credit');
                $saldo  = $acc->normal_side === 'debit'
                    ? $acc->current_balance + $debit - $credit
                    : $acc->current_balance - $debit + $credit;
                $total += $saldo;
                $rows->push([$acc->code . ' — ' . $acc->name, $saldo]);
            }
            $rows->push(['Total ' . $label, $total]);
            $rows->push(['', '']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Keterangan', 'Jumlah (Rp)'];
    }

    public function title(): string
    {
        return 'Neraca';
    }
}