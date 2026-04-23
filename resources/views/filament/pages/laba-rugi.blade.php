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
        <div class="flex gap-3 mt-3">
            <a href="{{ route('laporan.laba-rugi.pdf', ['year' => $period_year, 'month' => $period_month]) }}"
            target="_blank"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                Export PDF
            </a>
            <a href="{{ route('laporan.laba-rugi.excel', ['year' => $period_year, 'month' => $period_month]) }}"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                Export Excel
            </a>
        </div>
        <div class="mt-8 max-w-3xl mx-auto">

            {{-- Header --}}
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase">RSU. BANYUMANIK 2</h2>
                <h3 class="text-lg font-semibold">LAPORAN LABA RUGI</h3>
                <p class="text-sm text-gray-500">
                    Periode : {{ \Carbon\Carbon::create($period_year, $period_month)->translatedFormat('F Y') }}
                </p>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                {{-- PENDAPATAN --}}
                <div class="bg-teal-700 text-white px-4 py-2 font-bold text-sm uppercase tracking-wide">
                    Pendapatan
                </div>

                @foreach($reportData['pendapatan'] as $item)
                    <div class="flex justify-between px-6 py-2 text-sm border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="text-gray-600 dark:text-gray-300">
                            {{ $item['account']->code }} — {{ $item['account']->name }}
                        </span>
                        <span class="font-medium text-gray-800 dark:text-gray-100">
                            Rp {{ number_format($item['jumlah'], 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach

                {{-- Total Pendapatan --}}
                <div class="flex justify-between px-6 py-2 bg-teal-50 dark:bg-teal-900 font-bold text-sm border-b-2 border-teal-600">
                    <span>Total Pendapatan</span>
                    <span class="text-teal-700 dark:text-teal-300">
                        Rp {{ number_format($reportData['total_pendapatan'], 0, ',', '.') }}
                    </span>
                </div>

                {{-- BEBAN --}}
                <div class="bg-red-700 text-white px-4 py-2 font-bold text-sm uppercase tracking-wide">
                    Beban
                </div>

                @foreach($reportData['beban'] as $item)
                    <div class="flex justify-between px-6 py-2 text-sm border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="text-gray-600 dark:text-gray-300">
                            {{ $item['account']->code }} — {{ $item['account']->name }}
                        </span>
                        <span class="font-medium text-red-600 dark:text-red-400">
                            Rp {{ number_format($item['jumlah'], 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach

                {{-- Total Beban --}}
                <div class="flex justify-between px-6 py-2 bg-red-50 dark:bg-red-900 font-bold text-sm border-b-2 border-red-600">
                    <span>Total Beban</span>
                    <span class="text-red-600 dark:text-red-400">
                        Rp {{ number_format($reportData['total_beban'], 0, ',', '.') }}
                    </span>
                </div>

                {{-- LABA / RUGI --}}
                @php $labaRugi = $reportData['laba_rugi']; @endphp
                <div class="flex justify-between px-6 py-3 font-bold text-base {{ $labaRugi >= 0 ? 'bg-green-50 dark:bg-green-900' : 'bg-red-50 dark:bg-red-900' }}">
                    <span>{{ $labaRugi >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</span>
                    <span class="{{ $labaRugi >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                        Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>