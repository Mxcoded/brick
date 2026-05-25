<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('logged_out_at');
            $table->index('last_activity_at');
        });

        // Set existing active sessions' last_activity to their login time
        DB::table('user_login_logs')
            ->whereNull('logged_out_at')
            ->update(['last_activity_at' => DB::raw('logged_in_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_login_logs', function (Blueprint $table) {
            $table->dropIndex(['last_activity_at']);
            $table->dropColumn('last_activity_at');
        });
    }
};
