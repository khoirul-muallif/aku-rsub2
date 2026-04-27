<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h2, h3 { text-align: center; margin: 4px 0; }
        p { text-align: center; color: #666; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #0f766e; color: white; padding: 5px 8px; text-align: left; font-size: 10px; }
        th.text-right { text-align: right; }
        td { padding: 4px 8px; border-bottom: 1px solid #f0f0f0; }
        td.text-right { text-align: right; }
        .total-row { background: #e2f8f5; font-weight: bold; border-top: 2px solid #0f766e; }
        .red { color: #dc2626; }
        .summary { display: table; width: 100%; margin: 12px 0; }
        .summary-cell { display: table-cell; text-align: center; padding: 6px; border: 1px solid #e5e7eb; }
        .summary-label { font-size: 9px; color: #6b7280; }
        .summary-value { font-weight: bold; font-size: 11px; }
    </style>
</head>
<body>
    <h2>RSU. BANYUMANIK 2</h2>
    <h3>AGING {{ strtoupper($type === 'receivable' ? 'PIUTANG' : 'HUTANG') }}</h3>
    <p>Per Tanggal : {{ $as_of_date }}</p>

    {{-- Summary --}}
    <div class="summary">
        @foreach($buckets as $key => $bucket)
        <div class="summary-cell">
            <div class="summary-label">{{ $bucket['label'] }}</div>
            <div class="summary-value">Rp {{ number_format($grand_total[$key], 0, ',', '.') }}</div>
        </div>
        @endforeach
        <div class="summary-cell" style="background:#f3f4f6">
            <div class="summary-label">TOTAL</div>
            <div class="summary-value">Rp {{ number_format($grand_total['total'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="20%">Nama</th>
                @if($type === 'receivable')
                    <th width="10%">Penjamin</th>
                @endif
                @foreach($buckets as $bucket)
                    <th class="text-right">{{ $bucket['label'] }}</th>
                @endforeach
                <th class="text-right" width="13%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            <tr style="{{ $i % 2 === 0 ? '' : 'background:#f9fafb' }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['name'] }}</td>
                @if($type === 'receivable')
                    <td>{{ $row['penjamin'] ?? '-' }}</td>
                @endif
                @foreach($buckets as $key => $bucket)
                    <td class="text-right {{ $row[$key] > 0 && $key !== 'current' ? 'red' : '' }}">
                        {{ $row[$key] > 0 ? 'Rp ' . number_format($row[$key], 0, ',', '.') : '-' }}
                    </td>
                @endforeach
                <td class="text-right" style="font-weight:bold">
                    Rp {{ number_format($row['total'], 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;color:#9ca3af;font-style:italic">
                    Tidak ada data outstanding
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="{{ $type === 'receivable' ? 3 : 2 }}">TOTAL</td>
                @foreach($buckets as $key => $bucket)
                    <td class="text-right">Rp {{ number_format($grand_total[$key], 0, ',', '.') }}</td>
                @endforeach
                <td class="text-right">Rp {{ number_format($grand_total['total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>