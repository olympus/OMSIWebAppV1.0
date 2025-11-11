<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSmsSendToServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('is_sms_send')->default('0')->comment('0 => default, 1 => sms not send, 2 => sms send');   
            
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
