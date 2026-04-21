<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = DB::select("SHOW INDEX FROM products");

        $indexNames = collect($indexes)->pluck('Key_name')->unique();

        if ($indexNames->contains('products_slug_unique')) {
            DB::statement('ALTER TABLE products DROP INDEX products_slug_unique');
        }

        if ($indexNames->contains('products_product_sku_unique')) {
            DB::statement('ALTER TABLE products DROP INDEX products_product_sku_unique');
        }

        if ($indexNames->contains('products_product_name_unique')) {
            DB::statement('ALTER TABLE products DROP INDEX products_product_name_unique');
        }
    }

    public function down(): void
    {
        Schema::table('products', function ($table) {
            $table->unique('slug');
            $table->unique('product_sku');
            $table->unique('product_name');
        });
    }
};