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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('created_by')->nullable()->after('status');
        });

        Schema::table('product_videos', function (Blueprint $table) {
            $table->integer('created_by')->nullable()->after('status');
        });

        Schema::table('product_information', function (Blueprint $table) {
            $table->text('file_url')->nullable()->after('description');
            $table->integer('created_by')->nullable()->after('status');
        });


        Schema::table('product_specialities', function (Blueprint $table) {
            $table->integer('created_by')->nullable()->after('status');
        });


        Schema::table('product_categories', function (Blueprint $table) {
            $table->integer('created_by')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
