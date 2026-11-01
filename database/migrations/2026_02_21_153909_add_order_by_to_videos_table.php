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
        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'order_by')) {
                $table->integer('order_by')->default(0)->after('enabled');
            }
        });

        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasIndex('videos', 'videos_order_by_index')) {
                $table->index('order_by', 'videos_order_by_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (Schema::hasIndex('videos', 'videos_order_by_index')) {
                $table->dropIndex('videos_order_by_index');
            }

            if (Schema::hasColumn('videos', 'order_by')) {
                $table->dropColumn('order_by');
            }
        });
    }
};
