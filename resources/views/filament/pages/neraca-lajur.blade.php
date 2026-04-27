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
            <button type="submit"
                class="px-5 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium rounded-lg transition">
                Generate Laporan
            </button>
            @if(count($reportData) > 0)
                <a href="{{ route('laporan.neraca-lajur.pdf', ['year' => $period_year, 'month' => $period_month]) }}"
                   target="_blank"
                   class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    Export PDF
                </a>
            @endif
        </div>
    </form>

    @if(count($reportData) > 0)
        <div class="mt-8">
            <div class="text-center mb-4">
                <h2 class="text-xl font-bold uppercase">RSU. BANYUMANIK 2</h2>
                <h3 class="text-lg font-semibold">NERACA LAJUR</h3>
                <p class="text-sm text-gray-500">
                    Periode : {{ \Carbon\Carbon::create($period_year, $period_month)->translatedFormat('F Y') }}
                </p>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-teal-700 text-white">
                                <th class="px-2 py-2 text-left w-16" rowspan="2">Kode</th>
                                <th class="px-2 py-2 text-left" rowspan="2">Nama Akun</th>
                                <th class="px-2 py-2 text-center" colspan="2">Neraca Saldo</th>
                                <th class="px-2 py-2 text-center" colspan="2">Laba Rugi</th>
                                <th class="px-2 py-2 text-center" colspan="2">Neraca</th>
                            </tr>
                            <tr class="bg-teal-600 text-white">
                                <th class="px-2 py-1 text-right">Debit</th>
                                <th class="px-2 py-1 text-right">Kredit</th>
                                <th class="px-2 py-1 text-right">Debit</th>
                                <th class="px-2 py-1 text-right">Kredit</th>
                                <th class="px-2 py-1 text-right">Debit</th>
                                <th class="px-2 py-1 text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($reportData['rows'] as $i => $row)
                                <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                                    <td class="px-2 py-1 font-mono text-gray-500">{{ $row['code'] }}</td>
                                    <td class="px-2 py-1">{{ $row['name'] }}</td>
                                    <td class="px-2 py-1 text-right text-blue-600">
                                        {{ $row['ns_debit'] > 0 ? number_format($row['ns_debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-2 py-1 text-right text-red-500">
                                        {{ $row['ns_credit'] > 0 ? number_format($row['ns_credit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-2 py-1 text-right text-blue-600">
                                        {{ $row['lr_debit'] > 0 ? number_format($row['lr_debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-2 py-1 text-right text-red-500">
                                        {{ $row['lr_credit'] > 0 ? number_format($row['lr_credit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-2 py-1 text-right text-blue-600">
                                        {{ $row['ner_debit'] > 0 ? number_format($row['ner_debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-2 py-1 text-right text-red-500">
                                        {{ $row['ner_credit'] > 0 ? number_format($row['ner_credit'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Laba/Rugi berjalan --}}
                            @php $lr = $reportData['laba_rugi']; @endphp
                            <tr class="{{ $lr >= 0 ? 'bg-green-50 dark:bg-green-900' : 'bg-red-50 dark:bg-red-900' }}">
                                <td colspan="2" class="px-2 py-1 font-bold">
                                    {{ $lr >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}
                                </td>
                                <td colspan="2"></td>
                                <td class="px-2 py-1 text-right font-bold {{ $lr < 0 ? 'text-blue-600' : '' }}">
                                    {{ $lr < 0 ? number_format(abs($lr), 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1 text-right font-bold {{ $lr >= 0 ? 'text-red-500' : '' }}">
                                    {{ $lr >= 0 ? number_format($lr, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1 text-right font-bold {{ $lr >= 0 ? 'text-blue-600' : '' }}">
                                    {{ $lr >= 0 ? number_format($lr, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1 text-right font-bold {{ $lr < 0 ? 'text-red-500' : '' }}">
                                    {{ $lr < 0 ? number_format(abs($lr), 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-teal-50 dark:bg-teal-900 font-bold border-t-2 border-teal-600 text-xs">
                                <td colspan="2" class="px-2 py-2">TOTAL</td>
                                <td class="px-2 py-2 text-right text-blue-600">
                                    {{ number_format($reportData['totals']['ns_debit'], 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-2 text-right text-red-500">
                                    {{ number_format($reportData['totals']['ns_credit'], 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-2 text-right text-blue-600">
                                    {{ number_format($reportData['totals']['lr_debit'] + ($reportData['laba_rugi'] < 0 ? abs($reportData['laba_rugi']) : 0), 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-2 text-right text-red-500">
                                    {{ number_format($reportData['totals']['lr_credit'] + ($reportData['laba_rugi'] >= 0 ? $reportData['laba_rugi'] : 0), 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-2 text-right text-blue-600">
                                    {{ number_format($reportData['totals']['ner_debit'] + ($reportData['laba_rugi'] >= 0 ? $reportData['laba_rugi'] : 0), 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-2 text-right text-red-500">
                                    {{ number_format($reportData['totals']['ner_credit'] + ($reportData['laba_rugi'] < 0 ? abs($reportData['laba_rugi']) : 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>