<?php

namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class LabaRugiExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private int $year,
        private int $month
    ) {}

    public function collection(): Collection
    {
        $rows = collect();
        $rows->push(['RSU. BANYUMANIK 2', '', '']);
        $rows->push(['LAPORAN LABA RUGI', '', '']);
        $rows->push([\Carbon\Carbon::create($this->year, $this->month)->translatedFormat('F Y'), '', '']);
        $rows->push(['', '', '']);
        $rows->push(['PENDAPATAN', '', '']);

        $totalPendapatan = 0;
        $pendapatan = Account::active()->where('type', 'revenue')->posting()
            ->with(['journalLines' => fn($q) => $q->whereHas('journal', fn($j) =>
                $j->where('period_year', $this->year)->where('period_month', $this->month)->where('status', 'posted')
            )])->orderBy('code')->get();

        foreach ($pendapatan as $acc) {
            $jumlah = $acc->journalLines->sum('credit') - $acc->journalLines->sum('debit');
            $totalPendapatan += $jumlah;
            $rows->push([$acc->code, $acc->name, $jumlah]);
        }

        $rows->push(['', 'Total Pendapatan', $totalPendapatan]);
        $rows->push(['', '', '']);
        $rows->push(['BEBAN', '', '']);

        $totalBeban = 0;
        $beban = Account::active()->where('type', 'expense')->posting()
            ->with(['journalLines' => fn($q) => $q->whereHas('journal', fn($j) =>
                $j->where('period_year', $this->year)->where('period_month', $this->month)->where('status', 'posted')
            )])->orderBy('code')->get();

        foreach ($beban as $acc) {
            $jumlah = $acc->journalLines->sum('debit') - $acc->journalLines->sum('credit');
            $totalBeban += $jumlah;
            $rows->push([$acc->code, $acc->name, $jumlah]);
        }

        $rows->push(['', 'Total Beban', $totalBeban]);
        $rows->push(['', '', '']);
        $rows->push(['', $totalPendapatan >= $totalBeban ? 'LABA BERSIH' : 'RUGI BERSIH', $totalPendapatan - $totalBeban]);

        return $rows;
    }

    public function headings(): array
    {
        return ['Kode', 'Keterangan', 'Jumlah (Rp)'];
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}