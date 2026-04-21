<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_products');
    }
};

