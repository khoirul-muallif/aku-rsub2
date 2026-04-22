<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Tipe jurnal
            $table->enum('journal_type', [
                'kas', 'bank', 'memo', 
                'pendapatan', 'pembiayaan'
            ])->default('memo')->after('reference_type');

            // Untuk jurnal kas & bank
            $table->string('account_bank_code', 50)
                ->nullable()
                ->after('journal_type')
                ->comment('Kode rekening bank/kas');
            
            // Kode pembantu (nama vendor/pasien/karyawan)
            $table->string('helper_code', 100)
                ->nullable()
                ->after('account_bank_code')
                ->comment('Kode pembantu: nama vendor, pasien, dll');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn([
                'journal_type', 
                'account_bank_code',
                'helper_code'
            ]);
        });
    }
};
