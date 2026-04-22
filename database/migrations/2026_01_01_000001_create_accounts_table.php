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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id()->unsigned();
            
            // COA Code & Description
            $table->string('code', 20)->unique()->comment('COA Code: 1.100, 1.110, 4.100');
            $table->string('name', 255)->comment('Nama akun');
            $table->text('description')->nullable()->comment('Detail penjelasan akun');
            
            // Classification
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])
                ->comment('Klasifikasi akun utama');
            
            // Hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete()
                ->comment('Parent untuk hierarchy');
            
            // Balance tracking
            $table->decimal('current_balance', 18, 2)->default(0)
                ->comment('Saldo akun (di-update setiap posting)');
            
            // Status & metadata
            $table->boolean('is_active')->default(true)->comment('Aktif/tidak aktif');
            $table->boolean('is_header')->default(false)->comment('TRUE jika group akun (tidak bisa posting langsung)');
            $table->enum('normal_side', ['debit', 'credit'])->nullable()
                ->comment('Sisi normal: asset/expense=debit, liability/revenue=credit');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('type');
            $table->index('parent_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
