<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDownloadExcelMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('download_excel_mails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();
            $table->boolean('status')->default('1')->comment('0 => in_active ,1 => active');
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
        Schema::dropIfExists('download_excel_mails');
    }
}
