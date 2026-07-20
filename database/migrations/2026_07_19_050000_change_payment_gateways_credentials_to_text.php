<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // credentials are encrypted at rest via the model cast, so they must be
        // stored as TEXT (an encrypted string), not JSON.
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->text('credentials')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->json('credentials')->nullable()->change();
        });
    }
};
