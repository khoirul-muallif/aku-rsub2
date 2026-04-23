<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com"></script>

    <form wire:submit="generate">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tahun</label>
            <select wire:model="period_year" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @foreach(range(now()->year, now()->year - 5) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Bulan</label>
            <select wire:model="period_month" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mt-4 flex gap-3">
        <button type="submit" class="px-5 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium rounded-lg transition">
            Generate Laporan
        </button>
        @if(count($reportData) > 0)
            <a href="{{ route('laporan.neraca.pdf', ['year' => $period_year, 'month' => $period_month]) }}"
               target="_blank"
               class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                Export PDF
            </a>
            <a href="{{ route('laporan.neraca.excel', ['year' => $period_year, 'month' => $period_month]) }}"
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                Export Excel
            </a>
        @endif
    </div>
</form>

    @if(count($reportData) > 0)
        
        <div class="mt-8 max-w-5xl mx-auto">

            {{-- Header --}}
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase">RSU. BANYUMANIK 2</h2>
                <h3 class="text-lg font-semibold">NERACA</h3>
                <p class="text-sm text-gray-500">
                    Per : {{ \Carbon\Carbon::create($period_year, $period_month)->translatedFormat('F Y') }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-6">

                {{-- KIRI: ASET --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="bg-teal-700 text-white px-4 py-2 font-bold text-sm uppercase">Aset</div>

                    @foreach($reportData['asset']['list'] as $item)
                        <div class="flex justify-between px-4 py-2 text-sm border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-300">{{ $item['account']->code }} — {{ $item['account']->name }}</span>
                            <span class="font-medium">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach

                    <div class="flex justify-between px-4 py-2 bg-teal-50 dark:bg-teal-900 font-bold text-sm">
                        <span>Total Aset</span>
                        <span class="text-teal-700 dark:text-teal-300">Rp {{ number_format($reportData['asset']['total'], 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- KANAN: KEWAJIBAN + EKUITAS --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                    {{-- Kewajiban --}}
                    <div class="bg-red-700 text-white px-4 py-2 font-bold text-sm uppercase">Kewajiban</div>

                    @foreach($reportData['liability']['list'] as $item)
                        <div class="flex justify-between px-4 py-2 text-sm border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-300">{{ $item['account']->code }} — {{ $item['account']->name }}</span>
                            <span class="font-medium text-red-600 dark:text-red-400">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach

                    <div class="flex justify-between px-4 py-2 bg-red-50 dark:bg-red-900 font-bold text-sm border-b-2 border-red-300">
                        <span>Total Kewajiban</span>
                        <span class="text-red-600">Rp {{ number_format($reportData['liability']['total'], 0, ',', '.') }}</span>
                    </div>

                    {{-- Ekuitas --}}
                    <div class="bg-blue-700 text-white px-4 py-2 font-bold text-sm uppercase">Ekuitas</div>

                    @foreach($reportData['equity']['list'] as $item)
                        <div class="flex justify-between px-4 py-2 text-sm border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-300">{{ $item['account']->code }} — {{ $item['account']->name }}</span>
                            <span class="font-medium text-blue-600 dark:text-blue-400">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach

                    {{-- Laba/Rugi Berjalan --}}
                    @php $lr = $reportData['laba_rugi_berjalan']; @endphp
                    <div class="flex justify-between px-4 py-2 text-sm border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-300">Laba/Rugi Tahun Berjalan</span>
                        <span class="font-medium {{ $lr >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $lr >= 0 ? '' : '-' }}Rp {{ number_format(abs($lr), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between px-4 py-2 bg-blue-50 dark:bg-blue-900 font-bold text-sm border-b-2 border-blue-300">
                        <span>Total Ekuitas</span>
                        <span class="text-blue-600">Rp {{ number_format($reportData['equity']['total'] + $lr, 0, ',', '.') }}</span>
                    </div>

                    {{-- Total Kewajiban + Ekuitas --}}
                    <div class="flex justify-between px-4 py-3 bg-gray-100 dark:bg-gray-800 font-bold text-sm">
                        <span>Total Kewajiban + Ekuitas</span>
                        <span class="text-gray-800 dark:text-gray-100">Rp {{ number_format($reportData['total_liability_equity'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Cek balance --}}
            @php
                $selisih = $reportData['asset']['total'] - $reportData['total_liability_equity'];
            @endphp
            <div class="mt-4 text-center text-sm {{ $selisih == 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $selisih == 0 ? '✓ Neraca Seimbang' : '⚠ Neraca Tidak Seimbang — Selisih: Rp ' . number_format(abs($selisih), 0, ',', '.') }}
            </div>
        </div>
    @endif
</x-filament-panels::page>