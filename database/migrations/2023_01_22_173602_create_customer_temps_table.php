<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_temps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_id')->nullable();
            $table->string('sap_customer_id')->nullable();
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('email')->nullable();
            $table->integer('otp_code')->nullable();
            $table->timestamp('valid_upto')->nullable(); 
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_testing')->default(false);
            $table->text('password')->nullable(); 
            $table->string('hospital_id')->nullable();
            $table->text('device_token')->nullable();
            $table->string('platform')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_expired')->default(false)->comment('0 => not_expired ,1 => expired'); 
            $table->timestamp('password_updated_at')->nullable(); 
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
        Schema::dropIfExists('customer_temps');
    }
}
