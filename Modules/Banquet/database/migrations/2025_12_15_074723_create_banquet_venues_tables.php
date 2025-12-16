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
        // 1. Create Venues Table (Replaces hardcoded 'Adamawa Hall', etc.)
        if (!Schema::hasTable('banquet_venues')) {
            Schema::create('banquet_venues', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // e.g., "Adamawa Hall"
                $table->integer('capacity')->nullable();
                $table->decimal('rate_per_hour', 10, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Create Combinations Table (Solves the 'Adamawa + Kano' logic)
        if (!Schema::hasTable('banquet_venue_combinations')) {
            Schema::create('banquet_venue_combinations', function (Blueprint $table) {
                $table->id();
                // The "Parent" is the combined venue (e.g., Adamawa + Kano)
                $table->foreignId('parent_venue_id')->constrained('banquet_venues')->onDelete('cascade');
                // The "Child" is the individual part (e.g., Adamawa)
                $table->foreignId('child_venue_id')->constrained('banquet_venues')->onDelete('cascade');
            });
        }

        // 3. Create Setup Styles Table (Replaces hardcoded style arrays)
        if (!Schema::hasTable('banquet_setup_styles')) {
            Schema::create('banquet_setup_styles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // e.g., "Theater Style"
                $table->string('image_path')->nullable(); // For showing diagrams later
                $table->timestamps();
            });
        }

        // 4. Update existing Order Days table to link to these new tables
        if (Schema::hasTable('banquet_order_days')) {
            Schema::table('banquet_order_days', function (Blueprint $table) {
                // Add nullable FKs. We will migrate data from the string columns to these IDs later.
                if (!Schema::hasColumn('banquet_order_days', 'banquet_venue_id')) {
                    $table->foreignId('banquet_venue_id')->nullable()->constrained('banquet_venues')->onDelete('set null');
                }
                if (!Schema::hasColumn('banquet_order_days', 'banquet_setup_style_id')) {
                    $table->foreignId('banquet_setup_style_id')->nullable()->constrained('banquet_setup_styles')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns from banquet_order_days first
        if (Schema::hasTable('banquet_order_days')) {
            Schema::table('banquet_order_days', function (Blueprint $table) {
                if (Schema::hasColumn('banquet_order_days', 'banquet_venue_id')) {
                    $table->dropForeign(['banquet_venue_id']);
                    $table->dropColumn('banquet_venue_id');
                }
                if (Schema::hasColumn('banquet_order_days', 'banquet_setup_style_id')) {
                    $table->dropForeign(['banquet_setup_style_id']);
                    $table->dropColumn('banquet_setup_style_id');
                }
            });
        }

        Schema::dropIfExists('banquet_setup_styles');
        Schema::dropIfExists('banquet_venue_combinations');
        Schema::dropIfExists('banquet_venues');
    }
};
