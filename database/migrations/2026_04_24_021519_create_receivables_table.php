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
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->string('debtor_name');
            $table->string('penjamin', 100)->nullable()->comment('Asuransi/Umum/BPJS');
            $table->enum('type', ['RI', 'RJ', 'lain'])->default('RJ');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->decimal('amount', 18, 2)->default(0)->comment('Total tagihan');
            $table->decimal('paid_amount', 18, 2)->default(0)->comment('Sudah dibayar');
            $table->decimal('discount', 18, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('invoice_date');
            $table->index('debtor_name');
            $table->index('penjamin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
