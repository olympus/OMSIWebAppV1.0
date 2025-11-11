<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcademicRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('academic_request', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cvm_id')->nullable();
            $table->string('request_type')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('hospital_id')->nullable();
            $table->string('dept_id')->nullable();
            $table->text('remarks')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('academic_request');
    }
}
