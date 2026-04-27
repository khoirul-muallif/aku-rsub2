<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        h2, h3 { text-align: center; margin: 3px 0; }
        p { text-align: center; color: #666; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #0f766e; color: white; padding: 4px 6px; text-align: center; border: 1px solid #0d9488; }
        td { padding: 3px 6px; border-bottom: 1px solid #f0f0f0; }
        td.text-right { text-align: right; }
        td.text-left { text-align: left; }
        .total-row { background: #e2f8f5; font-weight: bold; border-top: 2px solid #0f766e; }
        .laba-row { background: #dcfce7; font-weight: bold; }
        .rugi-row { background: #fee2e2; font-weight: bold; }
        .blue { color: #1d4ed8; }
        .red { color: #dc2626; }
        .mono { font-family: monospace; }
    </style>
</head>
<body>
    <h2>RSU. BANYUMANIK 2</h2>
    <h3>NERACA LAJUR</h3>
    <p>Periode : {{ $periode }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:6%">Kode</th>
                <th rowspan="2" style="width:22%;text-align:left">Nama Akun</th>
                <th colspan="2">Neraca Saldo</th>
                <th colspan="2">Laba Rugi</th>
                <th colspan="2">Neraca</th>
            </tr>
            <tr>
                <th style="width:10%">Debit</th>
                <th style="width:10%">Kredit</th>
                <th style="width:10%">Debit</th>
                <th style="width:10%">Kredit</th>
                <th style="width:11%">Debit</th>
                <th style="width:11%">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
            <tr style="{{ $i % 2 === 0 ? '' : 'background:#f9fafb' }}">
                <td class="mono text-left">{{ $row['account']->code }}</td>
                <td class="text-left">{{ $row['account']->name }}</td>
                <td class="text-right blue">{{ $row['nsDebit'] > 0 ? number_format($row['nsDebit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right red">{{ $row['nsCredit'] > 0 ? number_format($row['nsCredit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right blue">{{ $row['lrDebit'] > 0 ? number_format($row['lrDebit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right red">{{ $row['lrCredit'] > 0 ? number_format($row['lrCredit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right blue">{{ $row['nerDebit'] > 0 ? number_format($row['nerDebit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right red">{{ $row['nerCredit'] > 0 ? number_format($row['nerCredit'], 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach

            {{-- Laba/Rugi --}}
            @php $lr = $labaRugi; @endphp
            <tr class="{{ $lr >= 0 ? 'laba-row' : 'rugi-row' }}">
                <td colspan="2" class="text-left">{{ $lr >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}</td>
                <td colspan="2"></td>
                <td class="text-right blue">{{ $lr < 0 ? number_format(abs($lr), 0, ',', '.') : '-' }}</td>
                <td class="text-right red">{{ $lr >= 0 ? number_format($lr, 0, ',', '.') : '-' }}</td>
                <td class="text-right blue">{{ $lr >= 0 ? number_format($lr, 0, ',', '.') : '-' }}</td>
                <td class="text-right red">{{ $lr < 0 ? number_format(abs($lr), 0, ',', '.') : '-' }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-left">TOTAL</td>
                <td class="text-right blue">{{ number_format($totals['ns_debit'], 0, ',', '.') }}</td>
                <td class="text-right red">{{ number_format($totals['ns_credit'], 0, ',', '.') }}</td>
                <td class="text-right blue">{{ number_format($totals['lr_debit'] + ($lr < 0 ? abs($lr) : 0), 0, ',', '.') }}</td>
                <td class="text-right red">{{ number_format($totals['lr_credit'] + ($lr >= 0 ? $lr : 0), 0, ',', '.') }}</td>
                <td class="text-right blue">{{ number_format($totals['ner_debit'] + ($lr >= 0 ? $lr : 0), 0, ',', '.') }}</td>
                <td class="text-right red">{{ number_format($totals['ner_credit'] + ($lr < 0 ? abs($lr) : 0), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>