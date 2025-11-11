<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSFDCLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfdc_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->string('previous_status');
            $table->string('new_status');
            $table->integer('splits');
            $table->string('employee_code');
            $table->string('action');
            $table->text('response');
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('service_requests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sfdc_logs');
    }
}
