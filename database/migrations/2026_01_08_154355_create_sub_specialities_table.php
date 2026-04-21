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
        Schema::create('sub_specialities', function (Blueprint $table) {
            $table->id(); 

            $table->foreignId('speciality_id')
                  ->nullable()
                  ->constrained('specialities')
                  ->nullOnDelete(); 

            $table->string('sub_specialities_name')->nullable();
            $table->string('sub_specialities_image')->nullable();
            $table->string('sub_specialities_image_url')->nullable();

            $table->longText('sub_specialities_description')->nullable();

            $table->boolean('is_trending')->default(0);
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('sub_specialities');
    }
};
