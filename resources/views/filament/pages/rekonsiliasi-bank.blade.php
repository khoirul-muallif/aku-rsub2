<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Filter --}}
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1">Akun Bank</label>
            <select wire:model.live="account_id" wire:change="generate"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">-- Pilih Akun Bank --</option>
                @foreach(\App\Models\Account::active()->where('code', 'like', '1.1%')->posting()->orderBy('code')->get() as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tahun</label>
            <select wire:model.live="period_year" wire:change="generate"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @foreach(range(now()->year, now()->year - 5) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Bulan</label>
            <select wire:model.live="period_month" wire:change="generate"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($account_id && count($reportData) > 0)

        <div class="grid grid-cols-2 gap-6">

            {{-- KIRI: Saldo Buku --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-teal-700 text-white px-4 py-2 font-bold text-sm">
                    SALDO MENURUT BUKU
                </div>
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Saldo Awal</span>
                        <span>Rp {{ number_format($reportData['saldo_awal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Debit</span>
                        <span class="text-blue-600">Rp {{ number_format($reportData['mutasi_debit_buku'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Kredit</span>
                        <span class="text-red-500">Rp {{ number_format($reportData['mutasi_kredit_buku'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold border-t pt-2">
                        <span>Saldo Akhir Buku</span>
                        <span class="text-teal-700">Rp {{ number_format($reportData['saldo_buku'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- KANAN: Saldo Bank --}}
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-blue-700 text-white px-4 py-2 font-bold text-sm">
                    SALDO MENURUT BANK
                </div>
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Debit Bank</span>
                        <span class="text-blue-600">Rp {{ number_format($reportData['total_bank_debit'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Kredit Bank</span>
                        <span class="text-red-500">Rp {{ number_format($reportData['total_bank_kredit'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold border-t pt-2">
                        <span>Saldo Akhir Bank</span>
                        <span class="text-blue-700">Rp {{ number_format($reportData['saldo_bank'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Selisih --}}
        @php $selisih = $reportData['selisih']; @endphp
        <div class="mt-4 p-4 rounded-xl border-2 {{ $selisih == 0 ? 'border-green-500 bg-green-50 dark:bg-green-900' : 'border-red-500 bg-red-50 dark:bg-red-900' }}">
            <div class="flex justify-between items-center">
                <span class="font-bold text-lg">
                    {{ $selisih == 0 ? '✓ REKONSILIASI SEIMBANG' : '⚠ ADA SELISIH' }}
                </span>
                <span class="font-bold text-xl {{ $selisih == 0 ? 'text-green-700' : 'text-red-700' }}">
                    Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Input Mutasi Bank --}}
        <div class="mt-6 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
            <div class="bg-gray-100 dark:bg-gray-800 px-4 py-2 font-bold text-sm border-b border-gray-200">
                + Input Mutasi Bank
            </div>
            <div class="p-4">
                <div class="grid grid-cols-6 gap-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal</label>
                        <input type="date" wire:model="tx_date"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium mb-1">Keterangan</label>
                        <input type="text" wire:model="tx_desc" placeholder="Uraian transaksi"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Debit</label>
                        <input type="number" wire:model="tx_debit" placeholder="0"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kredit</label>
                        <input type="number" wire:model="tx_credit" placeholder="0"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Saldo Akhir</label>
                        <input type="number" wire:model="tx_balance" placeholder="0"
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    </div>
                </div>
                <button wire:click="addStatement"
                    class="mt-3 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium rounded-lg">
                    + Tambah Mutasi
                </button>
            </div>
        </div>

        {{-- Tabel Mutasi Bank --}}
        @if($reportData['statements']->count() > 0)
            <div class="mt-4 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-blue-700 text-white px-4 py-2 font-bold text-sm">
                    Mutasi Bank
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-left">Keterangan</th>
                            <th class="px-3 py-2 text-right">Debit</th>
                            <th class="px-3 py-2 text-right">Kredit</th>
                            <th class="px-3 py-2 text-right">Saldo</th>
                            <th class="px-3 py-2 text-center w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($reportData['statements'] as $i => $stmt)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                                <td class="px-3 py-2">{{ $stmt->transaction_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ $stmt->description }}</td>
                                <td class="px-3 py-2 text-right text-blue-600">
                                    {{ $stmt->debit > 0 ? 'Rp '.number_format($stmt->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-3 py-2 text-right text-red-500">
                                    {{ $stmt->credit > 0 ? 'Rp '.number_format($stmt->credit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-3 py-2 text-right font-medium">
                                    {{ $stmt->balance > 0 ? 'Rp '.number_format($stmt->balance, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button wire:click="deleteStatement({{ $stmt->id }})"
                                        class="text-red-500 hover:text-red-700 text-xs">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-blue-50 dark:bg-blue-900 font-bold border-t-2 border-blue-600">
                        <tr>
                            <td colspan="2" class="px-3 py-2">Total</td>
                            <td class="px-3 py-2 text-right text-blue-600">
                                Rp {{ number_format($reportData['total_bank_debit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-right text-red-500">
                                Rp {{ number_format($reportData['total_bank_kredit'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                Rp {{ number_format($reportData['saldo_bank'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="mt-4 p-4 text-center text-gray-400 italic text-sm border border-dashed border-gray-300 rounded-xl">
                Belum ada mutasi bank. Input mutasi bank di atas untuk mulai rekonsiliasi.
            </div>
        @endif

    @else
        <div class="mt-6 p-6 text-center text-gray-400 italic border border-dashed border-gray-300 rounded-xl">
            Pilih akun bank dan periode untuk memulai rekonsiliasi.
        </div>
    @endif

</x-filament-panels::page>