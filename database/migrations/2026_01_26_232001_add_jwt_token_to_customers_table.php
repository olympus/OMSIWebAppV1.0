<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('jwt_token')->nullable()->after('is_mpin');
            $table->timestamp('account_verify_at')->nullable()->after('jwt_token');
            $table->boolean('is_account_block')
                  ->default(0)
                  ->comment('0 => unblock, 1 => block')
                  ->after('account_verify_at');
        });

        Schema::table('customer_temps', function (Blueprint $table) {
            $table->text('jwt_token')->nullable()->after('is_mpin');
            $table->timestamp('account_verify_at')->nullable()->after('jwt_token');
            $table->boolean('is_account_block')
                  ->default(0)
                  ->comment('0 => unblock, 1 => block')
                  ->after('account_verify_at');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'jwt_token',
                'account_verify_at',
                'is_account_block'
            ]);
        });

        Schema::table('customer_temps', function (Blueprint $table) {
            $table->dropColumn([
                'jwt_token',
                'account_verify_at',
                'is_account_block'
            ]);
        });
    }
};
