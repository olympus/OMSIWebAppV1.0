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
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->integer('splits')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('action')->nullable();
            $table->text('response')->nullable();
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
        Schema::dropIfExists('sfdc_logs');
    }
}
