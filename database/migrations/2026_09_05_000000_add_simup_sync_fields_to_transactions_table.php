<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_synced_to_simup')->default(false)->after('payment_details')->index();
            $table->timestamp('synced_to_simup_at')->nullable()->after('is_synced_to_simup');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['is_synced_to_simup', 'synced_to_simup_at']);
        });
    }
};
