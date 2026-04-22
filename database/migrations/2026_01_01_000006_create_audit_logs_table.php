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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id()->unsigned();
            
            // What changed
            $table->string('table_name', 100)->comment('journals, journal_lines, accounts, etc');
            $table->unsignedBigInteger('record_id')->comment('ID dari record yang diubah');
            $table->string('action', 50)->comment('create, update, delete, post, reverse');
            
            // Changes in JSON format
            $table->json('old_values')->nullable()->comment('Nilai lama (JSON)');
            $table->json('new_values')->nullable()->comment('Nilai baru (JSON)');
            
            // Who & when
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('User yang melakukan aksi');
            $table->string('ip_address', 45)->nullable()->comment('IP address');
            $table->text('user_agent')->nullable()->comment('Browser info');
            
            // Timestamp (immutable)
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes (untuk query audit trail cepat)
            $table->index('table_name');
            $table->index('record_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['table_name', 'record_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
