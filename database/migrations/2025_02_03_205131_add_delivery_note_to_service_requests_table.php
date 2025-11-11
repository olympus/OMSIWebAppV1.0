<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryNoteToServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->integer('happy_code')->nullable()->after('reminder_count'); 
            $table->timestamp('happy_code_delivered_time')->nullable()->after('happy_code'); 
        });

        Schema::table('archive_service_requests', function (Blueprint $table) {
            $table->integer('happy_code')->nullable()->after('reminder_count'); 
            $table->timestamp('happy_code_delivered_time')->nullable()->after('happy_code');  
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
