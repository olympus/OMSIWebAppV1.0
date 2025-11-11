<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerNameToServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_requests', function (Blueprint $table) { 
            // $table->string('sap_customer_id')->nullable();
            // $table->string('customer_title')->nullable();
            // $table->string('customer_first_name')->nullable();
            // $table->string('customer_middle_name')->nullable();
            // $table->string('customer_last_name')->nullable();
            // $table->string('customer_mobile_number')->nullable();
            // $table->string('customer_email')->nullable();  
            // $table->string('customer_hospital_id')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            //
        });
    }
}
