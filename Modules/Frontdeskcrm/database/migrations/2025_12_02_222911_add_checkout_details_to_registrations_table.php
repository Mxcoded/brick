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
        Schema::table('registrations', function (Blueprint $table) {
            $table->timestamp('actual_checkout_at')->nullable()->after('check_out');
            $table->foreignId('checked_out_by_agent_id')->nullable()->after('finalized_by_agent_id')->constrained('users')->onDelete('set null');
        });
    }

    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['actual_checkout_at', 'checked_out_by_agent_id']);
        });
    }
};
