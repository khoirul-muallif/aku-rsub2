<?php

namespace App\Http\Controllers;

use App\Exports\LabaRugiExport;
use App\Exports\NeracaExport;
use App\Models\Account;
use App\Models\JournalLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    private function getLabaRugiData(int $year, int $month): array
    {
        $pendapatan = Account::active()->where('type', 'revenue')->posting()
            ->with(['journalLines' => fn($q) => $q->whereHas('journal', fn($j) =>
                $j->where('period_year', $year)->where('period_month', $month)->where('status', 'posted')
            )])->orderBy('code')->get();

        $beban = Account::active()->where('type', 'expense')->posting()
            ->with(['journalLines' => fn($q) => $q->whereHas('journal', fn($j) =>
                $j->where('period_year', $year)->where('period_month', $month)->where('status', 'posted')
            )])->orderBy('code')->get();

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

        return [
            'pendapatan'       => $pendapatanList,
            'beban'            => $bebanList,
            'total_pendapatan' => $totalPendapatan,
            'total_beban'      => $totalBeban,
            'laba_rugi'        => $totalPendapatan - $totalBeban,
            'year'             => $year,
            'month'            => $month,
        ];
    }

    public function labaRugiPdf(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $data  = $this->getLabaRugiData($year, $month);

        $pdf = Pdf::loadView('laporan.laba-rugi-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download("laba-rugi-{$year}-{$month}.pdf");
    }

    public function labaRugiExcel(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        return Excel::download(
            new LabaRugiExport($year, $month),
            "laba-rugi-{$year}-{$month}.xlsx"
        );
    }

    public function neracaPdf(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Data neraca (sama seperti di Neraca page)
        $types  = ['asset', 'liability', 'equity'];
        $result = [];
        foreach ($types as $type) {
            $accounts = Account::active()->where('type', $type)->posting()
                ->with(['journalLines' => fn($q) => $q->whereHas('journal', fn($j) =>
                    $j->where('period_year', $year)->where('period_month', $month)->where('status', 'posted')
                )])->orderBy('code')->get();

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

        $labaRugi = $this->getLabaRugiData($year, $month)['laba_rugi'];

        $data = [
            'asset'                  => $result['asset'],
            'liability'              => $result['liability'],
            'equity'                 => $result['equity'],
            'laba_rugi_berjalan'     => $labaRugi,
            'total_liability_equity' => $result['liability']['total'] + $result['equity']['total'] + $labaRugi,
            'year'                   => $year,
            'month'                  => $month,
        ];

        $pdf = Pdf::loadView('laporan.neraca-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download("neraca-{$year}-{$month}.pdf");
    }

    public function neracaExcel(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        return Excel::download(
            new NeracaExport($year, $month),
            "neraca-{$year}-{$month}.xlsx"
        );
    }

    public function bukuBesarPdf(Request $request)
    {
        $year      = $request->get('year', now()->year);
        $month     = $request->get('month', now()->month);
        $accountId = $request->get('account_id');

        $query = Account::active()
            ->with(['journalLines' => fn($q) => $q->whereHas('journal', fn($j) =>
                $j->where('period_year', $year)->where('period_month', $month)->where('status', 'posted')
            )->with('journal')])->orderBy('code');

        if ($accountId) {
            $query->where('id', $accountId);
        } else {
            $query->posting();
        }

        $reportData = [];
        foreach ($query->get() as $account) {
            $totalDebit  = $account->journalLines->sum('debit');
            $totalCredit = $account->journalLines->sum('credit');
            $saldoAkhir  = $account->normal_side === 'debit'
                ? $account->current_balance + $totalDebit - $totalCredit
                : $account->current_balance - $totalDebit + $totalCredit;

            $reportData[] = [
                'account'      => $account,
                'saldo_awal'   => $account->current_balance,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'saldo_akhir'  => $saldoAkhir,
                'lines'        => $account->journalLines,
            ];
        }

        $pdf = Pdf::loadView('laporan.buku-besar-pdf', compact('reportData', 'year', 'month'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("buku-besar-{$year}-{$month}.pdf");
    }
    public function cashFlowPdf(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $akunKas = Account::active()
            ->where('type', 'asset')
            ->where('code', 'like', '1.1%')
            ->posting()->pluck('id');

        $penerimaan = JournalLine::whereIn('account_id', $akunKas)
            ->whereHas('journal', fn($q) => $q
                ->where('period_year', $year)->where('period_month', $month)
                ->where('status', 'posted')->whereIn('journal_type', ['kas', 'bank']))
            ->where('debit', '>', 0)->with(['journal', 'account'])->get();

        $pengeluaran = JournalLine::whereIn('account_id', $akunKas)
            ->whereHas('journal', fn($q) => $q
                ->where('period_year', $year)->where('period_month', $month)
                ->where('status', 'posted')->whereIn('journal_type', ['kas', 'bank']))
            ->where('credit', '>', 0)->with(['journal', 'account'])->get();

        $totalPenerimaan  = $penerimaan->sum('debit');
        $totalPengeluaran = $pengeluaran->sum('credit');
        $saldoAwal        = Account::whereIn('id', $akunKas)->sum('current_balance');

        $data = compact('penerimaan', 'pengeluaran', 'totalPenerimaan',
            'totalPengeluaran', 'saldoAwal', 'year', 'month');
        $data['netCashFlow'] = $totalPenerimaan - $totalPengeluaran;
        $data['saldoAkhir']  = $saldoAwal + $data['netCashFlow'];

        $pdf = Pdf::loadView('laporan.cash-flow-pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download("cash-flow-{$year}-{$month}.pdf");
    }
    public function agingPdf(Request $request)
    {
        $type      = $request->get('type', 'receivable');
        $asOfDate  = $request->get('as_of_date', now()->toDateString());
        $asOf      = \Carbon\Carbon::parse($asOfDate);

        $buckets = [
            'current'  => ['label' => 'Current (0-30 hari)', 'min' => 0,   'max' => 30],
            'b31_60'   => ['label' => '31-60 hari',          'min' => 31,  'max' => 60],
            'b61_90'   => ['label' => '61-90 hari',          'min' => 61,  'max' => 90],
            'b91_180'  => ['label' => '91-180 hari',         'min' => 91,  'max' => 180],
            'over180'  => ['label' => '> 180 hari',          'min' => 181, 'max' => 99999],
        ];

        $records = $type === 'receivable'
            ? \App\Models\Receivable::unpaid()->with('account')->get()
            : \App\Models\Payable::unpaid()->with('account')->get();

        $result     = [];
        $grandTotal = array_fill_keys(array_keys($buckets), 0);
        $grandTotal['total'] = 0;

        foreach ($records as $record) {
            $sisa = (float) $record->amount - (float) $record->paid_amount - (float) $record->discount;
            if ($sisa <= 0) continue;

            $days   = $asOf->diffInDays($record->invoice_date, false) * -1;
            if ($days < 0) $days = 0;

            $bucket = 'over180';
            foreach ($buckets as $key => $range) {
                if ($days >= $range['min'] && $days <= $range['max']) {
                    $bucket = $key;
                    break;
                }
            }

            $name = $type === 'receivable' ? $record->debtor_name : $record->creditor_name;

            if (!isset($result[$name])) {
                $result[$name] = [
                    'name'     => $name,
                    'penjamin' => $type === 'receivable' ? $record->penjamin : null,
                    'current'  => 0, 'b31_60' => 0, 'b61_90' => 0,
                    'b91_180'  => 0, 'over180' => 0, 'total'  => 0,
                ];
            }

            $result[$name][$bucket]    += $sisa;
            $result[$name]['total']    += $sisa;
            $grandTotal[$bucket]       += $sisa;
            $grandTotal['total']       += $sisa;
        }

        usort($result, fn($a, $b) => $b['total'] <=> $a['total']);

        $data = [
            'type'        => $type,
            'rows'        => array_values($result),
            'grand_total' => $grandTotal,
            'buckets'     => $buckets,
            'as_of_date'  => $asOf->translatedFormat('d F Y'),
        ];

        $pdf = Pdf::loadView('laporan.aging-pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download("aging-{$type}-{$asOfDate}.pdf");
    }
    public function neracaLajurPdf(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $accounts = Account::active()->posting()->orderBy('code')->get();
        $rows     = [];
        $totals   = [
            'ns_debit' => 0, 'ns_credit' => 0,
            'lr_debit' => 0, 'lr_credit' => 0,
            'ner_debit'=> 0, 'ner_credit'=> 0,
        ];

        foreach ($accounts as $account) {
            $lines = JournalLine::where('account_id', $account->id)
                ->whereHas('journal', fn($q) => $q
                    ->where('period_year', $year)
                    ->where('period_month', $month)
                    ->where('status', 'posted'))
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

            $mutasiDebit  = (float) ($lines->total_debit ?? 0);
            $mutasiKredit = (float) ($lines->total_credit ?? 0);
            $saldoAwal    = (float) $account->current_balance;

            if ($account->normal_side === 'debit') {
                $nsDebit  = $saldoAwal + $mutasiDebit;
                $nsCredit = $mutasiKredit;
                if ($nsDebit > $nsCredit) { $nsDebit -= $nsCredit; $nsCredit = 0; }
                else { $nsCredit -= $nsDebit; $nsDebit = 0; }
            } else {
                $nsCredit = $saldoAwal + $mutasiKredit;
                $nsDebit  = $mutasiDebit;
                if ($nsCredit > $nsDebit) { $nsCredit -= $nsDebit; $nsDebit = 0; }
                else { $nsDebit -= $nsCredit; $nsCredit = 0; }
            }

            if ($nsDebit == 0 && $nsCredit == 0) continue;

            $lrDebit = $lrCredit = $nerDebit = $nerCredit = 0;
            if (in_array($account->type, ['revenue', 'expense'])) {
                $lrDebit = $nsDebit; $lrCredit = $nsCredit;
            } else {
                $nerDebit = $nsDebit; $nerCredit = $nsCredit;
            }

            $rows[] = compact('account', 'nsDebit', 'nsCredit', 'lrDebit', 'lrCredit', 'nerDebit', 'nerCredit');
            $totals['ns_debit']  += $nsDebit;  $totals['ns_credit']  += $nsCredit;
            $totals['lr_debit']  += $lrDebit;  $totals['lr_credit']  += $lrCredit;
            $totals['ner_debit'] += $nerDebit; $totals['ner_credit'] += $nerCredit;
        }

        $labaRugi = $totals['lr_credit'] - $totals['lr_debit'];
        $periode  = \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');

        $pdf = Pdf::loadView('laporan.neraca-lajur-pdf',
            compact('rows', 'totals', 'labaRugi', 'periode'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("neraca-lajur-{$year}-{$month}.pdf");
    }
}