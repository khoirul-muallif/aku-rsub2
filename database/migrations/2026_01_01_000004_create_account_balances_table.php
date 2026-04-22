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
        Schema::create('account_balances', function (Blueprint $table) {
            $table->id()->unsigned();
            
            // Account & Period
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->integer('period_year')->comment('Tahun periode');
            $table->integer('period_month')->comment('Bulan periode (1-12)');
            
            // Balance amounts
            $table->decimal('opening_balance', 18, 2)->default(0)
                ->comment('Saldo awal bulan');
            $table->decimal('debit_amount', 18, 2)->default(0)
                ->comment('Total debit bulan ini');
            $table->decimal('credit_amount', 18, 2)->default(0)
                ->comment('Total credit bulan ini');
            $table->decimal('closing_balance', 18, 2)->default(0)
                ->comment('Saldo akhir bulan (opening + debit - credit)');
            
            // Timestamp
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Unique constraint (1 record per akun per bulan)
            $table->unique(['account_id', 'period_year', 'period_month']);
            
            // Indexes
            $table->index('account_id');
            $table->index(['period_year', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};
