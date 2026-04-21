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
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('mobile_otp')->nullable()->after('otp_code');  
            $table->integer('email_otp')->nullable()->after('mobile_otp');   
            $table->integer('is_face_id')->default('0')->comment('0 => not available, 1 => available')->after('app_version');  
            $table->integer('is_mpin')->default('0')->comment('0 => not available, 1 => available')->after('is_face_id'); 
        });

        Schema::table('customer_temps', function (Blueprint $table) {
            $table->integer('mobile_otp')->nullable()->after('otp_code');  
            $table->integer('email_otp')->nullable()->after('mobile_otp');   
            $table->integer('is_face_id')->default('0')->comment('0 => not available, 1 => available')->after('app_version');  
            $table->integer('is_mpin')->default('0')->comment('0 => not available, 1 => available')->after('is_face_id');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
