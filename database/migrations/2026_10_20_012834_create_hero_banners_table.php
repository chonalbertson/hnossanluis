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
        Schema::create('hero_banners', function (Blueprint $table) {
            $table->id();
            $table->string('label', 120);
            $table->string('eyebrow', 180)->nullable();
            $table->string('badge_metric', 100)->nullable();
            $table->string('title', 180);
            $table->string('highlight', 180)->nullable();
            $table->string('suffix', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('mobile_background_image_path')->nullable();
            $table->unsignedTinyInteger('overlay_opacity')->default(75);
            $table->string('primary_cta_label', 100)->nullable();
            $table->string('primary_cta_url', 255)->nullable();
            $table->string('secondary_cta_label', 100)->nullable();
            $table->string('secondary_cta_url', 255)->nullable();
            $table->string('accent', 30)->default('red');
            $table->string('product_source', 40)->default('destacados');
            $table->boolean('show_products_showcase')->default(true);
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_banners');
    }
};
