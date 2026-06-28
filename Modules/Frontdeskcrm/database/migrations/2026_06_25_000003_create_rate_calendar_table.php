<?php

use App\Models\RoomType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained((new RoomType)->getTable())->cascadeOnDelete();
            $table->date('date');
            $table->decimal('price', 10, 2)->nullable()->comment('Override price; null uses base from rate_code_prices');
            $table->unsignedTinyInteger('min_stay')->nullable()->comment('Minimum length of stay');
            $table->boolean('cta')->default(false)->comment('Closed to arrival');
            $table->boolean('ctd')->default(false)->comment('Closed to departure');
            $table->boolean('stop_sell')->default(false);
            $table->timestamps();

            $table->unique(['rate_code_id', 'room_type_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_calendar');
    }
};
