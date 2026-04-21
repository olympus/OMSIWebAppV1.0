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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id(); 

            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete(); 

            $table->string('sub_categories_name')->nullable();
            $table->string('sub_categories_image')->nullable();
            $table->longText('sub_categories_description')->nullable();

            $table->tinyInteger('status')->default(1);
            $table->integer('orderby')->nullable();
            $table->integer('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
