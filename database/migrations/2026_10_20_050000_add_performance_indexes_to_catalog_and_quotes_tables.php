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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'subcategory_id', 'deleted_at'], 'products_status_subcat_del_idx');
            $table->index(['status', 'brand_id', 'deleted_at'], 'products_status_brand_del_idx');
            $table->index(['status', 'updated_at'], 'products_status_updated_idx');
            $table->index(['status', 'material'], 'products_status_material_idx');
        });

        Schema::table('product_specifications', function (Blueprint $table) {
            $table->index(['product_id', 'sort_order'], 'product_specs_prod_sort_idx');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->index(['created_at'], 'quotes_created_at_idx');
            $table->index(['status', 'created_at'], 'quotes_status_created_idx');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'contact_messages_status_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex('contact_messages_status_created_idx');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex('quotes_status_created_idx');
            $table->dropIndex('quotes_created_at_idx');
        });

        Schema::table('product_specifications', function (Blueprint $table) {
            $table->dropIndex('product_specs_prod_sort_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_material_idx');
            $table->dropIndex('products_status_updated_idx');
            $table->dropIndex('products_status_brand_del_idx');
            $table->dropIndex('products_status_subcat_del_idx');
        });
    }
};
