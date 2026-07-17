<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('stats')->nullable()->comment('{meeting_rooms, total_sqm, total_capacity}');
            $table->string('brochure_pdf')->nullable();
            $table->string('equipment_heading')->nullable();
            $table->json('equipment_items')->nullable();
            $table->string('catering_heading')->nullable();
            $table->text('catering_description')->nullable();
            $table->string('catering_image_1')->nullable();
            $table->string('catering_image_2')->nullable();
            $table->string('catering_image_3')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_pages');
    }
};
