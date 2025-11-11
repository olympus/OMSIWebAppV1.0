<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRequestQuotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_quotations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_requests_id');
            $table->text('quo_filetype')->comment('pdf/xlsx/doc');
            $table->text('quo_type')->comment('Quotation/Technical Report');
            $table->text('quo_description')->nullable();
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
        Schema::dropIfExists('request_quotations');
    }
}
