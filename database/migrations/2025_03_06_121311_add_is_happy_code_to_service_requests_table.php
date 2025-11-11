<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsHappyCodeToServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('is_happy_code')->default('0')->comment('0 => otp not coming, 1 => otp is entered, 2 => Otp verified successfully');   
        });

        
        Schema::table('archive_service_requests', function (Blueprint $table) {
            $table->string('is_happy_code')->default('0')->comment('0 => otp not coming, 1 => otp is entered, 2 => Otp verified successfully');   
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
