<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->foreignId('subcategory_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // 🔥 Indexes for performance
            $table->index('product_id');
            $table->index('category_id');
            $table->index('subcategory_id');

            // 🚀 Prevent duplicate mappings
            $table->unique(
                ['product_id', 'category_id', 'subcategory_id'],
                'product_category_unique'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
};
