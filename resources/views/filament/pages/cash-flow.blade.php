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
                <a href="{{ route('laporan.cash-flow.pdf', ['year' => $period_year, 'month' => $period_month]) }}"
                   target="_blank"
                   class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    Export PDF
                </a>
            @endif
        </div>
    </form>

    @if(count($reportData) > 0)
        <div class="mt-8 max-w-3xl mx-auto">

            {{-- Header --}}
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase">RSU. BANYUMANIK 2</h2>
                <h3 class="text-lg font-semibold">LAPORAN ARUS KAS</h3>
                <p class="text-sm text-gray-500">
                    Periode : {{ \Carbon\Carbon::create($period_year, $period_month)->translatedFormat('F Y') }}
                </p>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">

                {{-- Saldo Awal --}}
                <div class="flex justify-between px-6 py-3 bg-gray-50 dark:bg-gray-800 font-medium text-sm border-b border-gray-200">
                    <span>Saldo Kas Awal Periode</span>
                    <span>Rp {{ number_format($reportData['saldo_awal'], 0, ',', '.') }}</span>
                </div>

                {{-- PENERIMAAN --}}
                <div class="bg-teal-700 text-white px-4 py-2 font-bold text-sm uppercase">
                    Penerimaan Kas
                </div>

                @foreach($reportData['penerimaan'] as $line)
                    <div class="flex justify-between px-6 py-2 text-sm border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="text-gray-600 dark:text-gray-300">
                            {{ $line->journal->journal_date->format('d/m/Y') }}
                            — {{ $line->journal->memo }}
                        </span>
                        <span class="font-medium text-teal-600">
                            Rp {{ number_format($line->debit, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach

                <div class="flex justify-between px-6 py-2 bg-teal-50 dark:bg-teal-900 font-bold text-sm border-b-2 border-teal-600">
                    <span>Total Penerimaan</span>
                    <span class="text-teal-700 dark:text-teal-300">
                        Rp {{ number_format($reportData['total_penerimaan'], 0, ',', '.') }}
                    </span>
                </div>

                {{-- PENGELUARAN --}}
                <div class="bg-red-700 text-white px-4 py-2 font-bold text-sm uppercase">
                    Pengeluaran Kas
                </div>

                @foreach($reportData['pengeluaran'] as $line)
                    <div class="flex justify-between px-6 py-2 text-sm border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="text-gray-600 dark:text-gray-300">
                            {{ $line->journal->journal_date->format('d/m/Y') }}
                            — {{ $line->journal->memo }}
                        </span>
                        <span class="font-medium text-red-600 dark:text-red-400">
                            Rp {{ number_format($line->credit, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach

                <div class="flex justify-between px-6 py-2 bg-red-50 dark:bg-red-900 font-bold text-sm border-b-2 border-red-600">
                    <span>Total Pengeluaran</span>
                    <span class="text-red-600 dark:text-red-400">
                        Rp {{ number_format($reportData['total_pengeluaran'], 0, ',', '.') }}
                    </span>
                </div>

                {{-- Net Cash Flow --}}
                @php $net = $reportData['net_cash_flow']; @endphp
                <div class="flex justify-between px-6 py-2 font-bold text-sm border-b border-gray-200 {{ $net >= 0 ? 'bg-green-50 dark:bg-green-900' : 'bg-red-50 dark:bg-red-900' }}">
                    <span>Kenaikan / Penurunan Kas Bersih</span>
                    <span class="{{ $net >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $net < 0 ? '-' : '' }}Rp {{ number_format(abs($net), 0, ',', '.') }}
                    </span>
                </div>

                {{-- Saldo Akhir --}}
                <div class="flex justify-between px-6 py-3 bg-gray-100 dark:bg-gray-800 font-bold text-base">
                    <span>Saldo Kas Akhir Periode</span>
                    <span class="text-gray-800 dark:text-gray-100">
                        Rp {{ number_format($reportData['saldo_akhir'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>