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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->json('attribute_schema')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'is_active', 'sort_order']);
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 64)->unique()->index();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('detailed_description')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('dimensions')->nullable();
            $table->string('material')->nullable();
            $table->string('sale_unit')->default('Unidad');
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('PEN');
            $table->string('price_visibility')->default('inherit'); // inherit, show, hide
            $table->string('availability_status')->default('available'); // available, on_request, out_of_stock, unknown
            $table->string('technical_pdf_path')->nullable();
            $table->string('status')->default('published'); // published, draft, archived
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id', 'brand_id'], 'products_status_cat_brand_idx');
            $table->index(['status', 'is_featured'], 'products_status_featured_idx');
            $table->index(['status', 'availability_status'], 'products_status_avail_idx');
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_primary', 'sort_order']);
        });

        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('spec_group')->default('General');
            $table->string('spec_key')->index();
            $table->string('spec_value')->index();
            $table->string('unit')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['spec_key', 'spec_value'], 'product_specs_key_value_idx');
        });

        Schema::create('product_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->default('application/pdf');
            $table->integer('downloads_count')->default(0);
            $table->timestamps();
        });

        Schema::create('product_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('filename');
            $table->string('status')->default('preview'); // preview, completed, failed
            $table->integer('total_rows')->default(0);
            $table->integer('to_create_count')->default(0);
            $table->integer('to_update_count')->default(0);
            $table->integer('created_count')->default(0);
            $table->integer('updated_count')->default(0);
            $table->integer('rejected_count')->default(0);
            $table->json('preview_rows')->nullable();
            $table->json('error_rows')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_import_batches');
        Schema::dropIfExists('product_documents');
        Schema::dropIfExists('product_specifications');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
