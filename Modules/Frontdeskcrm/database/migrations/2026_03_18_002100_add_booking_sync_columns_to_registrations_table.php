<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds columns to support Website Booking ↔ Front Desk CRM synchronization:
     * - original_check_in_date: Immutable record of the original booking date
     * - original_check_out_date: Immutable record of the original checkout date
     * - booking_group_id: Links registrations from the same group booking (GRP reference)
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Store original booking dates (immutable for audit trail)
            if (! Schema::hasColumn('registrations', 'original_check_in_date')) {
                $table->date('original_check_in_date')->nullable()->after('booking_id');
            }
            if (! Schema::hasColumn('registrations', 'original_check_out_date')) {
                $table->date('original_check_out_date')->nullable()->after('original_check_in_date');
            }

            // Link registrations from the same group booking (e.g., GRP-67890ABC)
            if (! Schema::hasColumn('registrations', 'booking_group_id')) {
                $table->string('booking_group_id')->nullable()->after('original_check_out_date');
                $table->index('booking_group_id');
            }

            // Track if dates were adjusted from original booking
            if (! Schema::hasColumn('registrations', 'dates_adjusted')) {
                $table->boolean('dates_adjusted')->default(false)->after('booking_group_id');
            }

            // Store the billing policy used (strict/flexible) for audit
            if (! Schema::hasColumn('registrations', 'billing_policy')) {
                $table->string('billing_policy')->nullable()->after('dates_adjusted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $columns = ['original_check_in_date', 'original_check_out_date', 'booking_group_id', 'dates_adjusted', 'billing_policy'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('registrations', $column)) {
                    if ($column === 'booking_group_id') {
                        $table->dropIndex(['booking_group_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
