<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Deposit / Advance Payment
            if (! Schema::hasColumn('registrations', 'deposit_required')) {
                $table->boolean('deposit_required')->default(false);
                $table->decimal('deposit_amount', 12, 2)->nullable();
                $table->timestamp('deposit_deadline')->nullable();
            }

            // Security Deposit
            if (! Schema::hasColumn('registrations', 'security_deposit_amount')) {
                $table->decimal('security_deposit_amount', 12, 2)->nullable();
                $table->timestamp('security_deposit_collected_at')->nullable();
                $table->timestamp('security_deposit_refunded_at')->nullable();
                $table->string('security_deposit_status', 20)->default('none');
            }

            // Pre-Authorization
            if (! Schema::hasColumn('registrations', 'pre_authorization_amount')) {
                $table->decimal('pre_authorization_amount', 12, 2)->nullable();
                $table->string('pre_authorization_reference', 100)->nullable();
                $table->string('pre_authorization_status', 20)->default('none');
                $table->timestamp('pre_authorization_expires_at')->nullable();
            }

            // Discount
            if (! Schema::hasColumn('registrations', 'discount_type')) {
                $table->string('discount_type', 20)->nullable();
                $table->decimal('discount_value', 12, 2)->nullable();
                $table->decimal('discount_percent', 5, 2)->nullable();
                $table->string('discount_reason', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $columns = [
                'deposit_required', 'deposit_amount', 'deposit_deadline',
                'security_deposit_amount', 'security_deposit_collected_at',
                'security_deposit_refunded_at', 'security_deposit_status',
                'pre_authorization_amount', 'pre_authorization_reference',
                'pre_authorization_status', 'pre_authorization_expires_at',
                'discount_type', 'discount_value', 'discount_percent', 'discount_reason',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('registrations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
