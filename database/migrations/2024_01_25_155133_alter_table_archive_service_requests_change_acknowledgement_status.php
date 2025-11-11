<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArchiveServiceRequestsChangeAcknowledgementStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('archive_service_requests', function (Blueprint $table) {
            $table->dropColumn('acknowledgement_status'); 
        });

        Schema::table('archive_service_requests', function (Blueprint $table) {
           $table->string('acknowledgement_status')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archive_service_requests', function (Blueprint $table) {
            //
        });
    }
}
