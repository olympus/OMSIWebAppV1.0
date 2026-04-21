<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->boolean('latest_product_show_in_popup')
                ->default(0)
                ->after('status')
                ->comment('0 = No, 1 = Yes');

            $table->boolean('is_notify')
                ->default(0)
                ->after('status')
                ->comment('0 = No, 1 = Yes');

        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn('latest_product_show_in_popup');
            $table->dropColumn('is_notify');

        });
    }
};
