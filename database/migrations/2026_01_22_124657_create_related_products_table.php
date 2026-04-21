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
        Schema::create('related_products', function (Blueprint $table) {

            $table->id();

            /* ================= SELF RELATION ================= */

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('compatible_product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->boolean('status')->default(true);

            $table->integer('orderby')->default(0);

            $table->timestamps();
            $table->softDeletes();

            /* ================= INDEXES ================= */

            $table->index(['product_id', 'status']);
            $table->index('orderby');

            /* ================= PREVENT DUPLICATE PAIR ================= */

            $table->unique(
                ['product_id', 'compatible_product_id'],
                'unique_related_products_pair'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('related_products');
    }
};
