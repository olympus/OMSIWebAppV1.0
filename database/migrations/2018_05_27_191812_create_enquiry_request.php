<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnquiryRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enquiry_request', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cvm_id')->nullable();
            $table->string('request_type')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('hospital_id')->nullable();
            $table->string('dept_id')->nullable();
            $table->text('remarks')->nullable();
            $table->string('sfdc_id')->nullable();
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
        Schema::dropIfExists('enquiry_request');
    }
}
