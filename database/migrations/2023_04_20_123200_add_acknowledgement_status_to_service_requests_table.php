<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAcknowledgementStatusToServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->integer('acknowledgement_status')->nullable(); 
            $table->timestamp('acknowledgement_updated_at')->nullable();  
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
