<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('deadline');
        });

        DB::statement("UPDATE tasks SET status = 'completed' WHERE is_completed = 1");
        DB::statement("UPDATE tasks SET status = 'pending' WHERE is_completed = 0 AND (is_successful IS NULL OR is_successful = 0)");
        DB::statement("UPDATE tasks SET status = 'in_progress' WHERE is_completed = 0 AND (is_successful IS NOT NULL AND is_successful = 1)");

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_completed', 'is_successful', 'meets_expectations', 'gm_notes']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('deadline');
            $table->boolean('is_successful')->nullable()->after('notes');
            $table->boolean('meets_expectations')->nullable()->after('is_successful');
            $table->text('gm_notes')->nullable()->after('meets_expectations');
        });

        DB::statement("UPDATE tasks SET is_completed = 1 WHERE status = 'completed'");
        DB::statement("UPDATE tasks SET is_completed = 0 WHERE status != 'completed'");

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
