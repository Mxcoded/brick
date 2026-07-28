<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->string('recurrence_type', 20)->nullable()->after('is_recurring');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_type');
            $table->unsignedBigInteger('parent_task_id')->nullable()->after('recurrence_end_date');
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_task_id']);
            $table->dropColumn(['is_recurring', 'recurrence_type', 'recurrence_end_date', 'parent_task_id']);
        });
    }
};
