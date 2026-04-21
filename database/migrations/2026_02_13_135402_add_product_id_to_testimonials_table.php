<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->foreignId('product_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('products')
                  ->onDelete('cascade');

            $table->string('video_type')
                  ->nullable()
                  ->after('thumbnail_image');
        });

        Schema::table('videos', function (Blueprint $table) { 
            $table->string('video_type')
                  ->nullable()
                  ->after('videos_thumbnail_image'); 

            $table->string('video_file')
                  ->nullable()
                  ->after('url');
            $table->string('url')
                  ->nullable()
                  ->change();
            $table->string('nt_title')
                  ->nullable()
                  ->change();
            $table->string('nt_description')
                  ->nullable()
                  ->change();
        });

        Schema::table('product_videos', function (Blueprint $table) { 
            $table->string('video_type')
                  ->nullable()
                  ->after('video_alt_text');  
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'video_type']);
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['video_type', 'video_file']);
        });

        Schema::table('product_videos', function (Blueprint $table) {
            $table->dropColumn('video_type');
        });
    }
};
