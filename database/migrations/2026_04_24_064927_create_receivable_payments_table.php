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
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receivable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->decimal('amount', 18, 2);
            $table->date('paid_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check'])->default('cash');
            $table->string('reference_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('receivable_id');
            $table->index('paid_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};
