<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerShowPromailersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_show_promailers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customers_id'); 
            $table->foreign('customers_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedInteger('promailers_id'); 
            $table->foreign('promailers_id')->references('id')->on('promailers')->onDelete('cascade'); 
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
        Schema::dropIfExists('customer_show_promailers');
    }
}
