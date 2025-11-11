<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestReminderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_reminder_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('customer_id');
            $table->string('previous_count')->nullable();
            $table->string('new_count')->nullable(); 
            $table->string('employee_code')->nullable(); 
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
        Schema::dropIfExists('request_reminder_histories');
    }
}
