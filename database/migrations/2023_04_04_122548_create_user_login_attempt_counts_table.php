<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLoginAttemptCountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_login_attempt_counts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('login_attempts')->default(0);
            $table->timestamp('login_attempts_updated_at')->nullable(); 
            $table->integer('forget_pwd_otp_attempts')->default(0); 
            $table->timestamp('otp_attempts_updated_at')->nullable();  
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
        Schema::dropIfExists('user_login_attempt_counts');
    }
}
