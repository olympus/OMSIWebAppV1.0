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
        Schema::create('roi_calculators', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('customer_id')->nullable();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();
 
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile', 15)->nullable();
            $table->string('hospital_name')->nullable();
            $table->string('speciality')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode', 6)->nullable();
            $table->string('customer_status')->nullable();
            $table->string('processor_profile')->nullable();
            $table->string('endoscopy_suite')->nullable();
            $table->string('procedure_performer')->nullable();
            $table->string('procedures_performed')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roi_calculators');
    }
};
