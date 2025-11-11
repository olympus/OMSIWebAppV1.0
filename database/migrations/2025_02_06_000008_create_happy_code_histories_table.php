<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHappyCodeHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('happy_code_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_requests_id')->nullable(); 
            $table->string('service_requests_status')->nullable(); 
            $table->integer('happy_code')->nullable(); 
            $table->timestamp('happy_code_delivered_time')->nullable(); 
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
        Schema::dropIfExists('happy_code_histories');
    }
}
