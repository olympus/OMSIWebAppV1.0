<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('archive_service_requests', function (Blueprint $table) {
            $table->string('is_sms_send')->default('0')->comment('0 => default, 1 => sms not send, 2 => sms send');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archive_service_requests', function (Blueprint $table) {
            //
        });
    }
};
