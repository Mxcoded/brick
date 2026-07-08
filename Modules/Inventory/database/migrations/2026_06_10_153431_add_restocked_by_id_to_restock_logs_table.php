<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restock_logs', function (Blueprint $table) {
            $table->foreignId('restocked_by_id')->nullable()->constrained('users')->nullOnDelete()->after('restocked_by');
        });

        DB::statement('UPDATE restock_logs SET restocked_by_id = (SELECT id FROM users WHERE name = restock_logs.restocked_by LIMIT 1) WHERE restocked_by IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('restock_logs', function (Blueprint $table) {
            $table->dropForeign(['restocked_by_id']);
            $table->dropColumn('restocked_by_id');
        });
    }
};
