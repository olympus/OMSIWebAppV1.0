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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('designation')->nullable();
            
            // Type: text or video
            $table->enum('type', ['text', 'video'])->default('text')->index();

            $table->text('message')->nullable();

            $table->string('thumbnail_image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_file')->nullable();

            $table->integer('order_by')->default(0)->index(); // sorting fast

            $table->boolean('status')->default(1)->index(); // filtering active
            $table->boolean('is_trending')->default(0)->index(); // trending filter
            
            $table->integer('created_by')->nullable();

            $table->softDeletes(); // deleted_at
            $table->timestamps();

            // Composite index (very useful for frontend listing)
            $table->index(['status', 'type', 'is_trending', 'order_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
