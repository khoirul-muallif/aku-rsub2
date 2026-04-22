<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fix #1: Disable FK checks sebelum truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('accounts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $accounts = [
            // AKTIVA LANCAR - Header
            [
                'code'        => '1.xxx',
                'name'        => 'AKTIVA LANCAR',
                'description' => 'Aktiva yang dapat dikonversi menjadi kas dalam 1 tahun',
                'type'        => 'asset',
                'parent_code' => null,
                'is_active'   => true,
                'is_header'   => true,
                'normal_side' => 'debit',
            ],
            ['code' => '1.100', 'name' => 'Kas Bendahara',               'type' => 'asset',     'parent_code' => '1.xxx', 'is_header' => false, 'normal_side' => 'debit',  'is_active' => true, 'description' => 'Kas yang dipegang bendahara/pejabat'],
            ['code' => '1.110', 'name' => 'Kas Kasir',                   'type' => 'asset',     'parent_code' => '1.xxx', 'is_header' => false, 'normal_side' => 'debit',  'is_active' => true, 'description' => 'Kas yang dipegang kasir di kasir'],
            ['code' => '1.120', 'name' => 'Bank Muamalat 5010117521',    'type' => 'asset',     'parent_code' => '1.xxx', 'is_header' => false, 'normal_side' => 'debit',  'is_active' => true, 'description' => 'Rekening bank untuk operasional umum'],
            ['code' => '1.121', 'name' => 'Bank Muamalat 5010117524',    'type' => 'asset',     'parent_code' => '1.xxx', 'is_header' => false, 'normal_side' => 'debit',  'is_active' => true, 'description' => 'Rekening bank khusus'],
            ['code' => '1.130', 'name' => 'Bank Syariah Indonesia',      'type' => 'asset',     'parent_code' => '1.xxx', 'is_header' => false, 'normal_side' => 'debit',  'is_active' => true, 'description' => 'Rekening bank BSI'],

            // LIABILITAS - Header
            ['code' => '2.xxx', 'name' => 'LIABILITAS',    'type' => 'liability', 'parent_code' => null,    'is_header' => true,  'normal_side' => 'credit', 'is_active' => true, 'description' => 'Kewajiban jangka panjang dan pendek'],
            ['code' => '2.100', 'name' => 'Hutang Gaji',   'type' => 'liability', 'parent_code' => '2.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Hutang untuk pembayaran gaji karyawan'],
            ['code' => '2.110', 'name' => 'Hutang Supplier','type' => 'liability', 'parent_code' => '2.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Hutang untuk pembelian barang dari supplier'],
            ['code' => '2.120', 'name' => 'Hutang Pajak',  'type' => 'liability', 'parent_code' => '2.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Hutang untuk pembayaran pajak'],

            // EKUITAS - Header
            ['code' => '3.xxx', 'name' => 'EKUITAS',       'type' => 'equity',   'parent_code' => null,    'is_header' => true,  'normal_side' => 'credit', 'is_active' => true, 'description' => 'Modal dan sisa hasil usaha'],
            ['code' => '3.100', 'name' => 'Modal Awal',    'type' => 'equity',   'parent_code' => '3.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Modal awal RSU'],
            ['code' => '3.110', 'name' => 'Laba Ditahan',  'type' => 'equity',   'parent_code' => '3.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Laba yang ditahan untuk operasional'],

            // PENDAPATAN - Header
            ['code' => '4.xxx', 'name' => 'PENDAPATAN',                      'type' => 'revenue', 'parent_code' => null,    'is_header' => true,  'normal_side' => 'credit', 'is_active' => true, 'description' => 'Pendapatan dari jasa kesehatan'],
            ['code' => '4.100', 'name' => 'Pendapatan Jasa Rawat Inap Umum', 'type' => 'revenue', 'parent_code' => '4.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Pendapatan dari pasien rawat inap umum'],
            ['code' => '4.101', 'name' => 'Pendapatan Jasa Rawat Inap Assuransi','type' => 'revenue','parent_code' => '4.xxx','is_header' => false,'normal_side' => 'credit','is_active' => true,'description' => 'Pendapatan dari pasien asuransi'],
            ['code' => '4.200', 'name' => 'Pendapatan Farmasi',              'type' => 'revenue', 'parent_code' => '4.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Pendapatan penjualan obat-obatan'],
            ['code' => '4.300', 'name' => 'Pendapatan Jasa Medis Lainnya',   'type' => 'revenue', 'parent_code' => '4.xxx', 'is_header' => false, 'normal_side' => 'credit', 'is_active' => true, 'description' => 'Pendapatan jasa medis (lab, radiologi, dll)'],

            // BEBAN - Header
            ['code' => '5.xxx', 'name' => 'BEBAN',                    'type' => 'expense', 'parent_code' => null,    'is_header' => true,  'normal_side' => 'debit', 'is_active' => true, 'description' => 'Beban operasional'],
            ['code' => '5.100', 'name' => 'Beban Gaji dan Upah',      'type' => 'expense', 'parent_code' => '5.xxx', 'is_header' => false, 'normal_side' => 'debit', 'is_active' => true, 'description' => 'Gaji karyawan tetap dan honorer'],
            ['code' => '5.110', 'name' => 'Beban Tunjangan',          'type' => 'expense', 'parent_code' => '5.xxx', 'is_header' => false, 'normal_side' => 'debit', 'is_active' => true, 'description' => 'Tunjangan kesehatan, makan, transport'],
            ['code' => '5.200', 'name' => 'Beban Operasional',        'type' => 'expense', 'parent_code' => '5.xxx', 'is_header' => false, 'normal_side' => 'debit', 'is_active' => true, 'description' => 'Listrik, air, telepon, supplies'],
            ['code' => '5.300', 'name' => 'Beban Pemeliharaan Gedung','type' => 'expense', 'parent_code' => '5.xxx', 'is_header' => false, 'normal_side' => 'debit', 'is_active' => true, 'description' => 'Pemeliharaan dan perbaikan gedung'],
            ['code' => '5.400', 'name' => 'Beban Penyusutan',         'type' => 'expense', 'parent_code' => '5.xxx', 'is_header' => false, 'normal_side' => 'debit', 'is_active' => true, 'description' => 'Penyusutan aset tetap'],
        ];

        // Fix #2: Insert header dulu, lalu resolve parent_id dari code
        // Step 1 — Insert semua header (parent_code = null)
        foreach ($accounts as $account) {
            if ($account['parent_code'] === null) {
                DB::table('accounts')->insert([
                    'code'        => $account['code'],
                    'name'        => $account['name'],
                    'description' => $account['description'],
                    'type'        => $account['type'],
                    'parent_id'   => null,
                    'is_active'   => $account['is_active'],
                    'is_header'   => $account['is_header'],
                    'normal_side' => $account['normal_side'],
                ]);
            }
        }

        // Step 2 — Insert child, lookup parent_id dari code
        foreach ($accounts as $account) {
            if ($account['parent_code'] !== null) {
                $parentId = DB::table('accounts')
                    ->where('code', $account['parent_code'])
                    ->value('id');

                DB::table('accounts')->insert([
                    'code'        => $account['code'],
                    'name'        => $account['name'],
                    'description' => $account['description'],
                    'type'        => $account['type'],
                    'parent_id'   => $parentId,
                    'is_active'   => $account['is_active'],
                    'is_header'   => $account['is_header'],
                    'normal_side' => $account['normal_side'],
                ]);
            }
        }

        $this->command->info('Chart of Accounts seeded successfully!');
    }
}
