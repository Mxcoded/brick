<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('pre_arrival_token', 64)->nullable()->unique()->after('guest_signature');
            $table->timestamp('pre_arrival_completed_at')->nullable()->after('pre_arrival_token');
            $table->text('special_requests')->nullable()->after('pre_arrival_completed_at');
            $table->timestamp('estimated_arrival_at')->nullable()->after('special_requests');
            $table->boolean('opt_in_marketing')->default(false)->after('estimated_arrival_at');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['pre_arrival_token', 'pre_arrival_completed_at', 'special_requests', 'estimated_arrival_at', 'opt_in_marketing']);
        });
    }
};
