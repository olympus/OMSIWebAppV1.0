<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoemailSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoemail_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('esc_level');
            $table->integer('user_level');
            $table->text('user_email');
            $table->text('team');
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
        Schema::dropIfExists('autoemail_settings');
    }
}
