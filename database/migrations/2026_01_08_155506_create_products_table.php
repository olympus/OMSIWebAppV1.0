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
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            /* ================= RELATIONS ================= */

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->foreignId('sub_category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->foreignId('speciality_id')
                ->nullable()
                ->constrained('specialities')
                ->nullOnDelete();

            $table->foreignId('sub_speciality_id')
                ->nullable()
                ->constrained('specialities')
                ->nullOnDelete();

            /* ================= PRODUCT DETAILS ================= */

            $table->string('product_name');
            $table->string('slug')->unique();
            $table->string('product_sku')->nullable()->unique();

            $table->string('heading')->nullable();
            $table->string('sub_heading')->nullable();

            $table->string('product_image')->nullable();
            $table->string('product_image_url')->nullable();
            $table->string('product_image_alt_text')->nullable();

            $table->longText('short_description')->nullable();
            $table->longText('long_description')->nullable();

            $table->boolean('is_trending')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('status')->default(true);

            $table->integer('orderby')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /* ================= INDEXES (IMPORTANT) ================= */

            $table->index(['category_id', 'sub_category_id']);
            $table->index(['speciality_id', 'sub_speciality_id']);
            $table->index(['status', 'is_trending']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
