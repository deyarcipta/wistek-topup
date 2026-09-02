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
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_nickname_check_enabled')->default(true)->after('status');
            $table->string('nickname_check_provider')->default('public')->after('is_nickname_check_enabled'); // public, digiflazz, disabled
            $table->string('digiflazz_inquiry_sku')->nullable()->after('nickname_check_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['is_nickname_check_enabled', 'nickname_check_provider', 'digiflazz_inquiry_sku']);
        });
    }
};
