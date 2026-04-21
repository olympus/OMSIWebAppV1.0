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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); 
            $table->integer('parent_id')->nullable();
            $table->integer('child_id')->nullable();
            $table->string('categories_name')->nullable();
            $table->string('slug')->nullable();
            $table->string('categories_image')->nullable();
            $table->text('categories_image_url')->nullable();
            $table->boolean('is_trending')->default(0);    
            $table->tinyInteger('status')->default(1);
            $table->integer('orderby')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
