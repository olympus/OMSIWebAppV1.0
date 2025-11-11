<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnquiryTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enquiry_timelines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_requests_id');
            $table->date('attended');
            $table->text('description')->nullable();
            $table->boolean('followup')->nullable();
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
        Schema::dropIfExists('enquiry_timelines');
    }
}
