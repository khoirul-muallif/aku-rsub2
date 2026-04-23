<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h2, h3 { text-align: center; margin: 4px 0; }
        p { text-align: center; color: #666; }
        .akun-block { margin-bottom: 20px; }
        .akun-header { background: #f3f4f6; padding: 5px 8px; font-size: 11px; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        th { background: #0f766e; color: white; padding: 4px 6px; text-align: left; }
        td { padding: 3px 6px; border-bottom: 1px solid #f0f0f0; }
        tfoot td { background: #e2f8f5; font-weight: bold; border-top: 2px solid #0f766e; }
        .text-right { text-align: right; }
        .text-blue { color: #1d4ed8; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>
    <h2>RSU. BANYUMANIK 2</h2>
    <h3>BUKU BESAR</h3>
    <p>Bulan : {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</p>

    @foreach($reportData as $data)
        @php $acc = $data['account']; @endphp
        <div class="akun-block">
            <div class="akun-header">
                No. Akun : <strong>{{ $acc->code }}</strong> &nbsp;|&nbsp;
                Nama Akun : <strong>{{ $acc->name }}</strong> &nbsp;|&nbsp;
                D/K : <strong>{{ strtoupper($acc->normal_side) }}</strong>
            </div>
            <table>
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="9%">Tgl</th>
                        <th width="14%">No. Jurnal</th>
                        <th>Keterangan</th>
                        <th class="text-right" width="13%">Saldo Awal</th>
                        <th class="text-right" width="12%">Debit</th>
                        <th class="text-right" width="12%">Kredit</th>
                        <th class="text-right" width="13%">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['lines'] as $i => $line)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $line->journal->journal_date->format('d/m/Y') }}</td>
                        <td>{{ $line->journal->journal_number }}</td>
                        <td>{{ $line->line_description }}</td>
                        <td class="text-right">
                            @if($i === 0) Rp {{ number_format($data['saldo_awal'], 0, ',', '.') }} @else - @endif
                        </td>
                        <td class="text-right text-blue">
                            {{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right text-red">
                            {{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($line->running_balance, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;color:#9ca3af;font-style:italic">
                            Tidak ada transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right">Jumlah</td>
                        <td class="text-right">Rp {{ number_format($data['saldo_awal'], 0, ',', '.') }}</td>
                        <td class="text-right text-blue">Rp {{ number_format($data['total_debit'], 0, ',', '.') }}</td>
                        <td class="text-right text-red">Rp {{ number_format($data['total_credit'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($data['saldo_akhir'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endforeach
</body>
</html>