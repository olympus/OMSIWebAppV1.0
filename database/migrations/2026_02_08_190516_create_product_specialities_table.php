<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_specialities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('speciality_id')
                ->nullable()
                ->constrained('specialities')
                ->cascadeOnDelete();

            $table->foreignId('sub_speciality_id')
                ->nullable()
                ->constrained('specialities')
                ->nullOnDelete();

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // 🔥 Important Indexes
            $table->index('product_id');
            $table->index('speciality_id');
            $table->index('sub_speciality_id');

            // 🚀 Prevent duplicate mapping
            $table->unique(
                ['product_id', 'speciality_id', 'sub_speciality_id'],
                'product_speciality_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specialities');
    }
};
