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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 64)->index();
            $table->string('name')->index();
            $table->string('variant_label')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('material')->nullable();
            $table->string('sale_unit')->default('Unidad');
            $table->string('box_qty', 60)->nullable();
            $table->string('carton_qty', 60)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('PEN');
            $table->string('availability_status')->default('available');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
