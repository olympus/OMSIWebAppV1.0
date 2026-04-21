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
        Schema::table('categories', function (Blueprint $table) {
            //
        });
 
        Schema::table('videos', function (Blueprint $table) { 
            $table->string('videos_thumbnail_image')->nullable()->after('title');
            $table->string('date')->nullable()->after('videos_thumbnail_image');
            $table->boolean('is_trending')->default(0)->after('date');    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            //
        });
    }
};
