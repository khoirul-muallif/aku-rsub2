<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalLine;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil akun yang ada
        $kas        = Account::where('code', '1.100')->first();
        $kasKasir   = Account::where('code', '1.110')->first();
        $bankMuam   = Account::where('code', '1.120')->first();
        $hutangGaji = Account::where('code', '2.100')->first();
        $gajiBeban  = Account::where('code', '5.100')->first();
        $opBeban    = Account::where('code', '5.200')->first();
        $pendRawat  = Account::where('code', '4.100')->first();
        $pendFarma  = Account::where('code', '4.200')->first();
        $pendJasa   = Account::where('code', '4.300')->first();

        $transaksi = [
            // Maret 2026
            [
                'date' => '2026-03-01', 'year' => 2026, 'month' => 3,
                'type' => 'kas', 'memo' => 'Penerimaan rawat inap Maret minggu 1',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kas, 'debit' => 15000000, 'credit' => 0, 'desc' => 'Terima kas rawat inap'],
                    ['account' => $pendRawat, 'debit' => 0, 'credit' => 15000000, 'desc' => 'Pendapatan rawat inap'],
                ],
            ],
            [
                'date' => '2026-03-05', 'year' => 2026, 'month' => 3,
                'type' => 'kas', 'memo' => 'Penerimaan rawat jalan Maret minggu 1',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kasKasir, 'debit' => 8500000, 'credit' => 0, 'desc' => 'Terima kas rawat jalan'],
                    ['account' => $pendJasa, 'debit' => 0, 'credit' => 8500000, 'desc' => 'Pendapatan jasa medis'],
                ],
            ],
            [
                'date' => '2026-03-07', 'year' => 2026, 'month' => 3,
                'type' => 'kas', 'memo' => 'Penerimaan farmasi Maret minggu 1',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kasKasir, 'debit' => 12000000, 'credit' => 0, 'desc' => 'Penerimaan farmasi'],
                    ['account' => $pendFarma, 'debit' => 0, 'credit' => 12000000, 'desc' => 'Pendapatan farmasi'],
                ],
            ],
            [
                'date' => '2026-03-10', 'year' => 2026, 'month' => 3,
                'type' => 'bank', 'memo' => 'Bayar gaji karyawan Maret',
                'status' => 'posted',
                'lines' => [
                    ['account' => $gajiBeban, 'debit' => 45000000, 'credit' => 0, 'desc' => 'Beban gaji Maret'],
                    ['account' => $bankMuam, 'debit' => 0, 'credit' => 45000000, 'desc' => 'Transfer gaji via bank'],
                ],
            ],
            [
                'date' => '2026-03-15', 'year' => 2026, 'month' => 3,
                'type' => 'kas', 'memo' => 'Penerimaan rawat inap Maret minggu 2',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kas, 'debit' => 18000000, 'credit' => 0, 'desc' => 'Terima kas rawat inap'],
                    ['account' => $pendRawat, 'debit' => 0, 'credit' => 18000000, 'desc' => 'Pendapatan rawat inap'],
                ],
            ],
            [
                'date' => '2026-03-20', 'year' => 2026, 'month' => 3,
                'type' => 'memo', 'memo' => 'Beban operasional Maret',
                'status' => 'posted',
                'lines' => [
                    ['account' => $opBeban, 'debit' => 5000000, 'credit' => 0, 'desc' => 'Beban operasional'],
                    ['account' => $kas, 'debit' => 0, 'credit' => 5000000, 'desc' => 'Keluar kas operasional'],
                ],
            ],
            [
                'date' => '2026-03-25', 'year' => 2026, 'month' => 3,
                'type' => 'kas', 'memo' => 'Penerimaan rawat jalan Maret minggu 3-4',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kasKasir, 'debit' => 22000000, 'credit' => 0, 'desc' => 'Terima kas rawat jalan'],
                    ['account' => $pendJasa, 'debit' => 0, 'credit' => 22000000, 'desc' => 'Pendapatan jasa medis'],
                ],
            ],

            // April 2026
            [
                'date' => '2026-04-02', 'year' => 2026, 'month' => 4,
                'type' => 'kas', 'memo' => 'Penerimaan rawat inap April minggu 1',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kas, 'debit' => 20000000, 'credit' => 0, 'desc' => 'Terima kas rawat inap'],
                    ['account' => $pendRawat, 'debit' => 0, 'credit' => 20000000, 'desc' => 'Pendapatan rawat inap'],
                ],
            ],
            [
                'date' => '2026-04-05', 'year' => 2026, 'month' => 4,
                'type' => 'kas', 'memo' => 'Penerimaan farmasi April minggu 1',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kasKasir, 'debit' => 9500000, 'credit' => 0, 'desc' => 'Penerimaan farmasi'],
                    ['account' => $pendFarma, 'debit' => 0, 'credit' => 9500000, 'desc' => 'Pendapatan farmasi'],
                ],
            ],
            [
                'date' => '2026-04-10', 'year' => 2026, 'month' => 4,
                'type' => 'bank', 'memo' => 'Bayar gaji karyawan April',
                'status' => 'posted',
                'lines' => [
                    ['account' => $gajiBeban, 'debit' => 45000000, 'credit' => 0, 'desc' => 'Beban gaji April'],
                    ['account' => $bankMuam, 'debit' => 0, 'credit' => 45000000, 'desc' => 'Transfer gaji via bank'],
                ],
            ],
            [
                'date' => '2026-04-15', 'year' => 2026, 'month' => 4,
                'type' => 'kas', 'memo' => 'Penerimaan rawat jalan April minggu 2',
                'status' => 'posted',
                'lines' => [
                    ['account' => $kasKasir, 'debit' => 14000000, 'credit' => 0, 'desc' => 'Terima kas rawat jalan'],
                    ['account' => $pendJasa, 'debit' => 0, 'credit' => 14000000, 'desc' => 'Pendapatan jasa medis'],
                ],
            ],
            [
                'date' => '2026-04-20', 'year' => 2026, 'month' => 4,
                'type' => 'memo', 'memo' => 'Beban operasional April',
                'status' => 'posted',
                'lines' => [
                    ['account' => $opBeban, 'debit' => 6500000, 'credit' => 0, 'desc' => 'Beban operasional April'],
                    ['account' => $kas, 'debit' => 0, 'credit' => 6500000, 'desc' => 'Keluar kas operasional'],
                ],
            ],

            // Draft untuk test Post/Edit/Delete
            [
                'date' => '2026-04-23', 'year' => 2026, 'month' => 4,
                'type' => 'kas', 'memo' => '[DRAFT] Penerimaan pasien - belum diposting',
                'status' => 'draft',
                'lines' => [
                    ['account' => $kas, 'debit' => 3000000, 'credit' => 0, 'desc' => 'Terima kas pasien'],
                    ['account' => $pendJasa, 'debit' => 0, 'credit' => 3000000, 'desc' => 'Pendapatan jasa'],
                ],
            ],
            [
                'date' => '2026-04-23', 'year' => 2026, 'month' => 4,
                'type' => 'bank', 'memo' => '[DRAFT] Bayar supplier obat - belum diposting',
                'status' => 'draft',
                'lines' => [
                    ['account' => $opBeban, 'debit' => 2500000, 'credit' => 0, 'desc' => 'Beban pembelian obat'],
                    ['account' => $bankMuam, 'debit' => 0, 'credit' => 2500000, 'desc' => 'Transfer ke supplier'],
                ],
            ],
        ];

        foreach ($transaksi as $t) {
            $totalDebit  = collect($t['lines'])->sum('debit');
            $totalCredit = collect($t['lines'])->sum('credit');

            $journal = Journal::create([
                'journal_number'   => Journal::generateJournalNumber(),
                'journal_date'     => $t['date'],
                'period_year'      => $t['year'],
                'period_month'     => $t['month'],
                'reference_type'   => 'manual',
                'journal_type'     => $t['type'],
                'memo'             => $t['memo'],
                'status'           => $t['status'],
                'total_debit'      => $totalDebit,
                'total_credit'     => $totalCredit,
                'posted_at'        => $t['status'] === 'posted' ? now() : null,
            ]);

            foreach ($t['lines'] as $i => $line) {
                JournalLine::create([
                    'journal_id'       => $journal->id,
                    'account_id'       => $line['account']->id,
                    'debit'            => $line['debit'],
                    'credit'           => $line['credit'],
                    'line_description' => $line['desc'],
                    'line_number'      => $i + 1,
                    'running_balance'  => 0,
                ]);
            }
        }

        $this->command->info('Dummy data berhasil dibuat! ' . count($transaksi) . ' jurnal.');
    }
}