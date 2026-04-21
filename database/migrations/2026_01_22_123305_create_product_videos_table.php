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
        Schema::create('product_videos', function (Blueprint $table) {

            $table->id();

            /* ================= RELATION ================= */

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            /* ================= VIDEO DETAILS ================= */

            $table->string('video_title')->nullable();

            $table->string('video_thumbnail')->nullable();
            $table->string('video_alt_text')->nullable();

            $table->string('video_url')->nullable();

            // If storing actual file path, string is better than longText
            $table->longText('video_file')->nullable();

            $table->boolean('status')->default(true);

            $table->integer('orderby')->default(0);

            $table->timestamps();
            $table->softDeletes();

            /* ================= INDEXES (IMPORTANT) ================= */

            $table->index(['product_id', 'status']);
            $table->index('orderby');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_videos');
    }
};
