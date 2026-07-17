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
        Schema::table('lead_events', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->unique()->after('slug');
            $table->text('confirmation_email_body')->nullable()->after('thank_you_message');
        });
    }

    public function down(): void
    {
        Schema::table('lead_events', function (Blueprint $table) {
            $table->dropColumn(['code', 'confirmation_email_body']);
        });
    }
};
