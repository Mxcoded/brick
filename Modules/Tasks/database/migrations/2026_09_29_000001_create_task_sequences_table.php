<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Task references were issued with count()+1, which handed out the same
     * number to two staff creating a task at the same moment, and re-issued a
     * number once its task was deleted. A persisted counter fixes both: the
     * number is only ever handed out once, and the row lock in
     * TaskNumberGenerator serialises concurrent callers.
     */
    public function up(): void
    {
        Schema::create('task_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 32)->unique();
            $table->unsignedBigInteger('next_number')->default(0);
            $table->timestamps();
        });

        // Seed today's counter from the highest reference already issued, so
        // the first task created after this migration does not collide with an
        // existing TASK-<date>-NNNN.
        $prefix = 'TASK-'.now()->format('dmy');

        $highest = DB::table('tasks')
            ->where('task_number', 'like', $prefix.'%')
            ->orderByDesc('task_number')
            ->value('task_number');

        DB::table('task_sequences')->insert([
            'prefix' => $prefix,
            'next_number' => $highest ? (int) substr($highest, -4) : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_sequences');
    }
};
