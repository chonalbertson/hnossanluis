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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 30)->nullable();
            $table->string('company')->nullable();
            $table->string('ruc', 11)->nullable()->index();
            $table->string('address')->nullable();
            $table->string('sector')->nullable();
            $table->boolean('is_authorized_contact')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 32)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contact_name');
            $table->string('contact_email')->index();
            $table->string('contact_phone', 30);
            $table->string('company_name')->nullable();
            $table->string('ruc', 11)->nullable()->index();
            $table->string('request_type', 20)->default('multiple'); // single, multiple
            $table->string('origin', 30)->default('web_catalog'); // web_catalog, product_detail, whatsapp, manual
            $table->string('status', 30)->default('nueva')->index(); // nueva, en_revision, pendiente_info, en_preparacion, respondida, aceptada, rechazada, cerrada
            $table->string('currency', 3)->default('PEN');
            $table->decimal('subtotal_offered', 12, 2)->nullable();
            $table->decimal('tax_offered', 12, 2)->nullable();
            $table->decimal('total_offered', 12, 2)->nullable();
            $table->text('customer_observations')->nullable();
            $table->longText('commercial_response')->nullable();
            $table->text('commercial_conditions')->nullable();
            $table->integer('validity_days')->default(15);
            $table->date('valid_until')->nullable();
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('last_email_idempotency_key')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_seller_id', 'created_at']);
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('sku_snapshot', 64);
            $table->string('product_name_snapshot');
            $table->string('sale_unit_snapshot')->default('Unidad');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price_offered', 12, 2)->nullable();
            $table->decimal('subtotal_offered', 12, 2)->nullable();
            $table->text('item_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('comment')->nullable();
            $table->boolean('notified_customer')->default(false);
            $table->timestamps();
        });

        Schema::create('internal_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_notes');
        Schema::dropIfExists('quote_status_histories');
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('customers');
    }
};
