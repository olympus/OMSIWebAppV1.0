<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {

            // old unique index drop
            $table->dropUnique('product_category_unique');

            // new unique index with deleted_at
            $table->unique(
                ['product_id', 'category_id', 'subcategory_id', 'deleted_at'],
                'product_category_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {

            $table->dropUnique('product_category_unique');

            $table->unique(
                ['product_id', 'category_id', 'subcategory_id'],
                'product_category_unique'
            );
        });
    }
};