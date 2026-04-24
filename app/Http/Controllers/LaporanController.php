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
}