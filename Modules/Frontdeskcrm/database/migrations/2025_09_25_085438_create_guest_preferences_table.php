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
        Schema::create('guest_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->json('preferences')->nullable();
            $table->string('language')->nullable()
                ->storedAs("JSON_UNQUOTE(preferences->'$.language')");
            $table->timestamps();

            $table->index('guest_id');
            $table->index('language'); // Indexing the generated column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_preferences');
    }
};
