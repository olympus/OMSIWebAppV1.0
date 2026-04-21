<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE product_specialities DROP INDEX product_speciality_unique');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE product_specialities 
            ADD UNIQUE product_speciality_unique (product_id, speciality_id, sub_speciality_id)');
    }
};