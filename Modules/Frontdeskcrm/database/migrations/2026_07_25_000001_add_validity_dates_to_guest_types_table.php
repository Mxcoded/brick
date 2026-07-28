<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_types', function (Blueprint $table) {
            $table->date('valid_from')->nullable()->after('discount_rate');
            $table->date('valid_to')->nullable()->after('valid_from');
        });
    }

    public function down(): void
    {
        Schema::table('guest_types', function (Blueprint $table) {
            $table->dropColumn(['valid_from', 'valid_to']);
        });
    }
};
