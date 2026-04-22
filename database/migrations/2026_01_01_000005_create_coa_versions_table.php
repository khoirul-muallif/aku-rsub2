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
        Schema::create('coa_versions', function (Blueprint $table) {
            $table->id()->unsigned();
            
            // Account & Period
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->integer('period_year')->comment('Tahun periode');
            $table->integer('period_month')->comment('Bulan periode (1-12)');
            
            // Changes tracking
            $table->string('old_name', 255)->nullable()->comment('Nama lama');
            $table->string('new_name', 255)->nullable()->comment('Nama baru');
            $table->text('old_description')->nullable();
            $table->text('new_description')->nullable();
            
            // Who & when
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('User yang ubah');
            $table->timestamp('changed_at')->useCurrent()
                ->comment('Saat perubahan');
            
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
        Schema::dropIfExists('coa_versions');
    }
};
