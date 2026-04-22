
<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com"></script>
    <form wire:submit="generate">
        {{ $this->form }}
        <div class="mt-4">
            <button type="submit" class="px-5 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium rounded-lg transition">
                Generate Laporan
            </button>
        </div>
    </form>

    @if(count($reportData) > 0)
        <div class="mt-8 space-y-8">

            <div class="text-center">
                <h2 class="text-xl font-bold uppercase tracking-wide">Buku Besar</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Bulan : {{ \Carbon\Carbon::create($period_year, $period_month)->translatedFormat('F Y') }}
                </p>
            </div>

            @foreach($reportData as $data)
                @php $acc = $data['account']; @endphp

                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                    {{-- Header Akun --}}
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex gap-8 text-sm">
                        <span class="text-gray-500">No. Akun : <strong class="text-gray-800 dark:text-gray-100">{{ $acc->code }}</strong></span>
                        <span class="text-gray-500">Nama Akun : <strong class="text-gray-800 dark:text-gray-100">{{ $acc->name }}</strong></span>
                        <span class="text-gray-500">D/K : <strong class="text-gray-800 dark:text-gray-100">{{ strtoupper($acc->normal_side) }}</strong></span>
                    </div>

                    {{-- Tabel --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-teal-700 text-white">
                                    <th class="px-3 py-2 text-left w-10">No</th>
                                    <th class="px-3 py-2 text-left w-24">Tgl</th>
                                    <th class="px-3 py-2 text-left w-36">No. Jurnal</th>
                                    <th class="px-3 py-2 text-left">Keterangan</th>
                                    <th class="px-3 py-2 text-right w-32">Saldo Awal</th>
                                    <th class="px-3 py-2 text-right w-32">Debit</th>
                                    <th class="px-3 py-2 text-right w-32">Kredit</th>
                                    <th class="px-3 py-2 text-right w-32">Saldo Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($data['lines'] as $i => $line)
                                    <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }} hover:bg-teal-50 dark:hover:bg-teal-900 transition">
                                        <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">
                                            {{ $line->journal->journal_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-3 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">
                                            {{ $line->journal->journal_number }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                            {{ $line->line_description }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300">
                                            @if($i === 0)
                                                Rp {{ number_format($data['saldo_awal'], 0, ',', '.') }}
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium text-blue-600 dark:text-blue-400">
                                            {{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium text-red-500 dark:text-red-400">
                                            {{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-gray-800 dark:text-gray-100">
                                            Rp {{ number_format($line->running_balance, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-6 text-center text-gray-400 italic">
                                            Tidak ada transaksi periode ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-teal-50 dark:bg-teal-900 font-bold border-t-2 border-teal-600">
                                    <td colspan="4" class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">
                                        Jumlah
                                    </td>
                                    <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">
                                        Rp {{ number_format($data['saldo_awal'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-blue-600 dark:text-blue-400">
                                        Rp {{ number_format($data['total_debit'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-red-500 dark:text-red-400">
                                        Rp {{ number_format($data['total_credit'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-gray-800 dark:text-gray-100">
                                        Rp {{ number_format($data['saldo_akhir'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>