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
            if (! Schema::hasColumn('users', 'tier_level')) {
                $table->string('tier_level')->default('regular')->after('points_balance');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'price_gold')) {
                $table->decimal('price_gold', 15, 2)->nullable()->after('price_sell');
            }
            if (! Schema::hasColumn('products', 'price_platinum')) {
                $table->decimal('price_platinum', 15, 2)->nullable()->after('price_gold');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'profit')) {
                $table->decimal('profit', 15, 2)->default(0)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tier_level')) {
                $table->dropColumn('tier_level');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'price_gold')) {
                $table->dropColumn(['price_gold', 'price_platinum']);
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'profit')) {
                $table->dropColumn('profit');
            }
        });
    }
};
