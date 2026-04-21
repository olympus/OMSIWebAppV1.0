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
        Schema::table('testimonials', function (Blueprint $table) {
            $table->integer('order_by')->nullable()->change();
        });

        Schema::table('product_information', function (Blueprint $table) {
            $table->integer('order')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::table('testimonials', function (Blueprint $table) {
            $table->integer('order_by')->nullable(false)->change();
        });

        Schema::table('product_information', function (Blueprint $table) {
            $table->integer('order')->nullable()->change();
        }); 
    }
};
