<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->string('payment_terms')->default('net_30');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('city_ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // charge, payment, adjustment
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('corporate_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('billing_to_account')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corporate_account_id');
            $table->dropColumn('billing_to_account');
        });
        Schema::dropIfExists('city_ledger_transactions');
        Schema::dropIfExists('corporate_accounts');
    }
};
