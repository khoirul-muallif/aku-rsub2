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
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id()->unsigned();
            
            // Foreign keys
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete()
                ->comment('FK ke journals.id');
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete()
                ->comment('FK ke accounts.id');
            
            // Amounts (debit XOR credit, tidak keduanya)
            $table->decimal('debit', 18, 2)->default(0)
                ->comment('Jumlah debit (atau 0 jika credit)');
            $table->decimal('credit', 18, 2)->default(0)
                ->comment('Jumlah credit (atau 0 jika debit)');
            
            // Description & ordering
            $table->text('line_description')->nullable()
                ->comment('Deskripsi spesifik per line');
            $table->integer('line_number')->comment('Urutan baris (1, 2, 3, dst)');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('journal_id');
            $table->index('account_id');
            $table->index(['journal_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
