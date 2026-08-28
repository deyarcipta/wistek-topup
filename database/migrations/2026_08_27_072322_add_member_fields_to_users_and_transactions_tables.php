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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('phone')->nullable()->after('username');
            $table->string('role')->default('member')->after('phone');
            $table->string('referral_code')->nullable()->unique()->after('role');
            $table->unsignedBigInteger('referred_by_id')->nullable()->after('referral_code');
            $table->string('registration_ip')->nullable()->after('referred_by_id');
            $table->integer('points_balance')->default(0)->after('registration_ip');

            $table->foreign('referred_by_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->integer('points_used')->default(0)->after('discount_amount');
            $table->integer('points_earned')->default(0)->after('points_used');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'points_used', 'points_earned']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_id']);
            $table->dropColumn([
                'username',
                'phone',
                'role',
                'referral_code',
                'referred_by_id',
                'registration_ip',
                'points_balance',
            ]);
        });
    }
};
