<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('item_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('from_unit', 50);
            $table->string('to_unit', 50);
            $table->decimal('conversion_rate', 12, 4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_conversions');
    }
};
