<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2, h3 { text-align: center; margin: 4px 0; }
        p { text-align: center; color: #666; margin: 2px 0; }
        .grid { display: table; width: 100%; margin-top: 16px; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding: 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f766e; color: white; padding: 5px 8px; text-align: left; }
        td { padding: 4px 8px; border-bottom: 1px solid #f0f0f0; }
        .section-header-red { background: #dc2626; color: white; font-weight: bold; padding: 5px 8px; }
        .section-header-blue { background: #1d4ed8; color: white; font-weight: bold; padding: 5px 8px; }
        .total-teal { background: #e2f8f5; font-weight: bold; }
        .total-red { background: #fee2e2; font-weight: bold; }
        .total-blue { background: #dbeafe; font-weight: bold; }
        .total-gray { background: #f3f4f6; font-weight: bold; font-size: 12px; }
        .text-right { text-align: right; }
        .balance-ok { text-align: center; color: green; font-weight: bold; margin-top: 12px; }
        .balance-err { text-align: center; color: red; font-weight: bold; margin-top: 12px; }
    </style>
</head>
<body>
    <h2>RSU. BANYUMANIK 2</h2>
    <h3>NERACA</h3>
    <p>Per : {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</p>

    <div class="grid">
        {{-- KIRI: ASET --}}
        <div class="col">
            <table>
                <tr><th colspan="2">ASET</th></tr>
                @foreach($asset['list'] as $item)
                <tr>
                    <td>{{ $item['account']->code }} — {{ $item['account']->name }}</td>
                    <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-teal">
                    <td>Total Aset</td>
                    <td class="text-right">Rp {{ number_format($asset['total'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- KANAN: KEWAJIBAN + EKUITAS --}}
        <div class="col">
            <table>
                <tr><td class="section-header-red" colspan="2">KEWAJIBAN</td></tr>
                @foreach($liability['list'] as $item)
                <tr>
                    <td>{{ $item['account']->code }} — {{ $item['account']->name }}</td>
                    <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-red">
                    <td>Total Kewajiban</td>
                    <td class="text-right">Rp {{ number_format($liability['total'], 0, ',', '.') }}</td>
                </tr>

                <tr><td class="section-header-blue" colspan="2">EKUITAS</td></tr>
                @foreach($equity['list'] as $item)
                <tr>
                    <td>{{ $item['account']->code }} — {{ $item['account']->name }}</td>
                    <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr>
                    <td>Laba/Rugi Tahun Berjalan</td>
                    <td class="text-right" style="color: {{ $laba_rugi_berjalan >= 0 ? 'green' : 'red' }}">
                        {{ $laba_rugi_berjalan < 0 ? '-' : '' }}Rp {{ number_format(abs($laba_rugi_berjalan), 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="total-blue">
                    <td>Total Ekuitas</td>
                    <td class="text-right">Rp {{ number_format($equity['total'] + $laba_rugi_berjalan, 0, ',', '.') }}</td>
                </tr>

                <tr class="total-gray">
                    <td>Total Kewajiban + Ekuitas</td>
                    <td class="text-right">Rp {{ number_format($total_liability_equity, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @php $selisih = $asset['total'] - $total_liability_equity; @endphp
    <p class="{{ $selisih == 0 ? 'balance-ok' : 'balance-err' }}">
        {{ $selisih == 0 ? '✓ Neraca Seimbang' : '⚠ Neraca Tidak Seimbang — Selisih: Rp ' . number_format(abs($selisih), 0, ',', '.') }}
    </p>
</body>
</html>