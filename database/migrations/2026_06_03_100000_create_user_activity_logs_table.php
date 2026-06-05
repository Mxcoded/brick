<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action');                        // page_view, create, update, delete, login, logout, export
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();        // GET, POST, PUT, DELETE
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('model_type')->nullable();        // e.g. Modules\Banquet\Models\BanquetOrder
            $table->unsignedBigInteger('model_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};