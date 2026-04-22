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
        Schema::create('journals', function (Blueprint $table) {
            $table->id()->unsigned();
            
            // Journal identification
            $table->string('journal_number', 50)->unique()
                ->comment('Format: JNL-2026-000001');
            
            // Date & Period
            $table->date('journal_date')->comment('Tanggal posting');
            $table->integer('period_year')->comment('Tahun periode');
            $table->integer('period_month')->comment('Bulan periode (1-12)');
            
            // Reference information
            $table->string('reference_type', 50)->comment('invoice, purchase_order, cash_receipt, etc');
            $table->string('reference_number', 100)->nullable()
                ->comment('No. dokumen: INV-001, PO-001');
            
            // Description
            $table->text('memo')->nullable()->comment('Keterangan singkat');
            $table->text('description')->nullable()->comment('Deskripsi lengkap transaksi');
            
            // Multi-reference tagging
            $table->string('unit_code', 50)->nullable()
                ->comment('Kode unit/departemen (RAW-INAP, FARMASI, etc)');
            $table->string('budget_code', 50)->nullable()
                ->comment('Kode budget allocation');
            $table->string('transaction_code', 50)->nullable()
                ->comment('Kode klasifikasi transaksi');
            
            // Workflow status
            $table->enum('status', ['draft', 'posted', 'reversed', 'pending_approval'])
                ->default('draft')
                ->comment('Status workflow');
            
            // Posting information
            $table->timestamp('posted_at')->nullable()->comment('Saat posting');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Approval information
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->comment('Saat approve');
            $table->text('rejection_reason')->nullable()->comment('Alasan ditolak');
            
            // Denormalized totals (untuk validasi cepat)
            $table->decimal('total_debit', 18, 2)->default(0)
                ->comment('SUM debit dari journal_lines');
            $table->decimal('total_credit', 18, 2)->default(0)
                ->comment('SUM credit dari journal_lines');
            
            // Timestamps & soft delete
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp');
            
            // Indexes
            $table->index('journal_date');
            $table->index(['period_year', 'period_month']);
            $table->index('status');
            $table->index(['reference_type', 'reference_number']);
            $table->index('unit_code');
            $table->index('posted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
