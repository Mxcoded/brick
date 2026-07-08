<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('min_points')->default(0);
            $table->decimal('multiplier', 5, 2)->default(1.00);
            $table->unsignedInteger('points_per_currency')->default(1);
            $table->string('color')->default('#CD7F32');
            $table->text('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->integer('points');
            $table->string('type'); // earned, redeemed, adjusted
            $table->string('description')->nullable();
            $table->decimal('spend_amount', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->foreignId('loyalty_tier_id')->nullable()->constrained('loyalty_tiers')->nullOnDelete();
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('lifetime_points')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loyalty_tier_id');
            $table->dropColumn(['total_points', 'lifetime_points']);
        });
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('loyalty_tiers');
    }
};
