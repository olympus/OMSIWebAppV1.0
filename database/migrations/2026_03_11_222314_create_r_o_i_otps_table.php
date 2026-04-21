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
        Schema::create('r_o_i_otps', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 15);
            $table->string('otp', 6);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_o_i_otps');
    }
};
