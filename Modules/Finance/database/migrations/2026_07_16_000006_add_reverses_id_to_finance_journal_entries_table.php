<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_journal_entries', function (Blueprint $table) {
            $table->foreignId('reverses_id')
                ->nullable()
                ->after('reference_id')
                ->constrained('finance_journal_entries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reverses_id');
        });
    }
};
