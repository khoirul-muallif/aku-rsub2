<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2, h3 { text-align: center; margin: 4px 0; }
        p { text-align: center; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #0f766e; color: white; padding: 6px 10px; text-align: left; }
        td { padding: 5px 10px; border-bottom: 1px solid #eee; }
        .section-header-green { background: #e2f8f5; font-weight: bold; }
        .section-header-red { background: #fee2e2; font-weight: bold; }
        .total-green { background: #dcfce7; font-weight: bold; }
        .total-red { background: #fee2e2; font-weight: bold; }
        .net { font-weight: bold; font-size: 13px; }
        .saldo { background: #f3f4f6; font-weight: bold; font-size: 13px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>RSU. BANYUMANIK 2</h2>
    <h3>LAPORAN ARUS KAS</h3>
    <p>Periode : {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</p>

    <table>
        <tr class="saldo">
            <td>Saldo Kas Awal Periode</td>
            <td class="text-right">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
        </tr>
        <tr><td class="section-header-green" colspan="2">PENERIMAAN KAS</td></tr>
        @foreach($penerimaan as $line)
        <tr>
            <td>{{ $line->journal->journal_date->format('d/m/Y') }} — {{ $line->journal->memo }}</td>
            <td class="text-right">Rp {{ number_format($line->debit, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr class="total-green">
            <td>Total Penerimaan</td>
            <td class="text-right">Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
        </tr>

        <tr><td class="section-header-red" colspan="2">PENGELUARAN KAS</td></tr>
        @foreach($pengeluaran as $line)
        <tr>
            <td>{{ $line->journal->journal_date->format('d/m/Y') }} — {{ $line->journal->memo }}</td>
            <td class="text-right">Rp {{ number_format($line->credit, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr class="total-red">
            <td>Total Pengeluaran</td>
            <td class="text-right">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
        </tr>

        <tr class="net">
            <td>Kenaikan / Penurunan Kas Bersih</td>
            <td class="text-right" style="color: {{ $netCashFlow >= 0 ? 'green' : 'red' }}">
                {{ $netCashFlow < 0 ? '-' : '' }}Rp {{ number_format(abs($netCashFlow), 0, ',', '.') }}
            </td>
        </tr>
        <tr class="saldo">
            <td>Saldo Kas Akhir Periode</td>
            <td class="text-right">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
        </tr>
    </table>
</body>
</html>