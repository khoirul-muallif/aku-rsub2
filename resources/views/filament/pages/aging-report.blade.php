<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com"></script>

    <form wire:submit="generate">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipe</label>
                <select wire:model="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="receivable">Piutang</option>
                    <option value="payable">Hutang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Per Tanggal</label>
                <input type="date" wire:model="as_of_date"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div class="mt-4 flex gap-3">
            <button type="submit"
                class="px-5 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium rounded-lg transition">
                Generate Laporan
            </button>
            @if(count($reportData) > 0)
                <a href="{{ route('laporan.aging.pdf', ['type' => $type, 'as_of_date' => $as_of_date]) }}"
                target="_blank"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    Export PDF
                </a>
            @endif
        </div>
    </form>

    @if(count($reportData) > 0)
        <div class="mt-8">
            {{-- Header --}}
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase">RSU. BANYUMANIK 2</h2>
                <h3 class="text-lg font-semibold">
                    AGING {{ $type === 'receivable' ? 'PIUTANG' : 'HUTANG' }}
                </h3>
                <p class="text-sm text-gray-500">Per Tanggal : {{ $reportData['as_of_date'] }}</p>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-5 gap-3 mb-6">
                @foreach($reportData['buckets'] as $key => $bucket)
                    <div class="border rounded-xl p-3 text-center {{ $key === 'current' ? 'border-green-300 bg-green-50 dark:bg-green-900' : ($key === 'over180' ? 'border-red-300 bg-red-50 dark:bg-red-900' : 'border-yellow-300 bg-yellow-50 dark:bg-yellow-900') }}">
                        <p class="text-xs text-gray-500 mb-1">{{ $bucket['label'] }}</p>
                        <p class="font-bold text-sm">
                            Rp {{ number_format($reportData['grand_total'][$key], 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Tabel --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-teal-700 text-white">
                                <th class="px-3 py-2 text-left">No</th>
                                <th class="px-3 py-2 text-left">Nama</th>
                                @if($type === 'receivable')
                                    <th class="px-3 py-2 text-left">Penjamin</th>
                                @endif
                                @foreach($reportData['buckets'] as $bucket)
                                    <th class="px-3 py-2 text-right">{{ $bucket['label'] }}</th>
                                @endforeach
                                <th class="px-3 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($reportData['rows'] as $i => $row)
                                <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                                    <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $row['name'] }}</td>
                                    @if($type === 'receivable')
                                        <td class="px-3 py-2 text-gray-500">{{ $row['penjamin'] ?? '-' }}</td>
                                    @endif
                                    @foreach($reportData['buckets'] as $key => $bucket)
                                        <td class="px-3 py-2 text-right {{ $row[$key] > 0 && $key !== 'current' ? 'text-red-600' : '' }}">
                                            {{ $row[$key] > 0 ? 'Rp ' . number_format($row[$key], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2 text-right font-bold">
                                        Rp {{ number_format($row['total'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-400 italic">
                                        Tidak ada data outstanding
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-teal-50 dark:bg-teal-900 font-bold border-t-2 border-teal-600">
                                <td colspan="{{ $type === 'receivable' ? 3 : 2 }}" class="px-3 py-2">
                                    TOTAL
                                </td>
                                @foreach($reportData['buckets'] as $key => $bucket)
                                    <td class="px-3 py-2 text-right">
                                        Rp {{ number_format($reportData['grand_total'][$key], 0, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-right">
                                    Rp {{ number_format($reportData['grand_total']['total'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>