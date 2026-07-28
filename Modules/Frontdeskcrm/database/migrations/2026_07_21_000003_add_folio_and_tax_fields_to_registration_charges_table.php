<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_charges', function (Blueprint $table) {
            $table->foreignId('folio_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tax_code')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('registration_charges', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->dropColumn(['folio_id', 'tax_code', 'tax_rate', 'tax_amount']);
        });
    }
};
