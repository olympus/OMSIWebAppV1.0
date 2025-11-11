<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAssignRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_requests', function (Blueprint $table) {
            
            $table->string('service_request_id')->index()->unique();
            $table->string('token')->unique();
            $table->timestamp('expired_at');
            $table->boolean('is_processed')->default('0');
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
        Schema::dropIfExists('assign_requests');
    }
}
