<?php

namespace Modules\Frontdeskcrm\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'rate_code_id')) {
                $table->foreignId('rate_code_id')->nullable()->constrained('rate_codes')->nullOnDelete();
            }
            if (! Schema::hasColumn('registrations', 'nights_posted')) {
                $table->integer('nights_posted')->default(0);
            }
            if (! Schema::hasColumn('registrations', 'last_audit_date')) {
                $table->date('last_audit_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rate_code_id');
            $table->dropColumn(['nights_posted', 'last_audit_date']);
        });
    }
};
