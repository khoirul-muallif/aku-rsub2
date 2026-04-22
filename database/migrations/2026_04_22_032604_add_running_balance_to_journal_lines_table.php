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
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->decimal('running_balance', 18, 2)
                ->default(0)
                ->after('credit')
                ->comment('Saldo berjalan per baris');
            
            $table->string('helper_code', 100)
                ->nullable()
                ->after('running_balance')
                ->comment('Kode pembantu per baris');
        });
    }

    public function down(): void
    {
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->dropColumn(['running_balance', 'helper_code']);
        });
    }
};
