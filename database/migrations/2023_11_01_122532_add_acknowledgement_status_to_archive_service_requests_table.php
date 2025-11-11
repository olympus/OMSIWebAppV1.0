<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAcknowledgementStatusToArchiveServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archive_service_requests', function (Blueprint $table) {
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
        Schema::table('archive_service_requests', function (Blueprint $table) {
            //
        });
    }
}
