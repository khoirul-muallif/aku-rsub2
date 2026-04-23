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
        .section-header { background: #e2f8f5; font-weight: bold; }
        .total-row { background: #f0fdf4; font-weight: bold; }
        .laba { background: #dcfce7; font-weight: bold; font-size: 13px; }
        .rugi { background: #fee2e2; font-weight: bold; font-size: 13px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>RSU. BANYUMANIK 2</h2>
    <h3>LAPORAN LABA RUGI</h3>
    <p>Periode : {{ \Carbon\Carbon::create($month, $month)->translatedFormat('F') }} {{ $year }}</p>

    <table>
        <tr class="section-header">
            <td colspan="3">PENDAPATAN</td>
        </tr>
        @foreach($pendapatan as $item)
        <tr>
            <td width="15%">{{ $item['account']->code }}</td>
            <td>{{ $item['account']->name }}</td>
            <td class="text-right" width="25%">Rp {{ number_format($item['jumlah'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2">Total Pendapatan</td>
            <td class="text-right">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</td>
        </tr>

        <tr class="section-header">
            <td colspan="3">BEBAN</td>
        </tr>
        @foreach($beban as $item)
        <tr>
            <td>{{ $item['account']->code }}</td>
            <td>{{ $item['account']->name }}</td>
            <td class="text-right">Rp {{ number_format($item['jumlah'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2">Total Beban</td>
            <td class="text-right">Rp {{ number_format($total_beban, 0, ',', '.') }}</td>
        </tr>

        <tr class="{{ $laba_rugi >= 0 ? 'laba' : 'rugi' }}">
            <td colspan="2">{{ $laba_rugi >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</td>
            <td class="text-right">Rp {{ number_format(abs($laba_rugi), 0, ',', '.') }}</td>
        </tr>
    </table>
</body>
</html>