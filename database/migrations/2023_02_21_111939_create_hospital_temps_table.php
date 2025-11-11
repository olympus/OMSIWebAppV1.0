<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHospitalTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospital_temps', function (Blueprint $table) {
            $table->increments('id'); 
            $table->string('hospital_name')->nullable(); 
            $table->string('dept_id')->nullable(); 
            $table->text('address')->nullable(); 
            $table->text('city')->nullable(); 
            $table->text('state')->nullable(); 
            $table->text('zip')->nullable(); 
            $table->text('country')->nullable(); 
            $table->string('responsible_branch')->default('GURGAON MAIN');
            $table->string('customer_id')->nullable();  
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
        Schema::dropIfExists('hospital_temps');
    }
}
