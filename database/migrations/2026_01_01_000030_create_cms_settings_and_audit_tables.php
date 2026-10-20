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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 30)->nullable();
            $table->string('company')->nullable();
            $table->string('ruc', 11)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('nuevo')->index(); // nuevo, leido, respondido
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_role')->nullable();
            $table->string('company_name')->nullable();
            $table->text('content');
            $table->tinyInteger('rating')->default(5);
            $table->boolean('is_verified')->default(false);
            $table->string('authorization_reference')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique(); // home, about, privacy_policy, cookies_policy, terms_conditions, quote_conditions
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('content');
            $table->json('sections_json')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_indexable')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_group')->default('general')->index();
            $table->string('setting_key')->unique();
            $table->longText('setting_value')->nullable();
            $table->string('setting_type', 20)->default('string'); // string, boolean, integer, json
            $table->string('label')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('module')->index();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('page_contents');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('contact_messages');
    }
};
