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
        if (!Schema::hasTable('banquet_orders')) {
            Schema::table('banquet_orders', function (Blueprint $table) {
                $table->text('special_instructions')->nullable();
                $table->decimal('other_charges', 10, 2)->default(0.00);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banquet_orders', function (Blueprint $table) {
            $table->dropColumn(['special_instructions', 'other_charges']);
        });
    }
};
