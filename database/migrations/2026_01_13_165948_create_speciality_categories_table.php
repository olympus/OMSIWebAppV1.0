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
        Schema::create('speciality_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('speciality_id')
                ->constrained('specialities')
                ->cascadeOnDelete();

            $table->foreignId('sub_speciality_id')
                ->nullable()
                ->constrained('specialities')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->foreignId('sub_category_id')
                ->nullable()
                ->constrained('categories')
                ->cascadeOnDelete();

            // Avoid reserved keyword
            $table->integer('orderby')->default(0);

            $table->boolean('status')->default(1);

            $table->timestamps();
            $table->softDeletes();

            /* ================= INDEXES (IMPORTANT) ================= */

            $table->index(['speciality_id', 'sub_speciality_id']);
            $table->index(['category_id', 'sub_category_id']);

            /* ================= PREVENT DUPLICATE MAPPING ================= */

            $table->unique([
                'speciality_id',
                'sub_speciality_id',
                'category_id',
                'sub_category_id'
            ], 'unique_speciality_category_map');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speciality_categories');
    }
};
