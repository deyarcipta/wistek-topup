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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->string('reference')->unique()->nullable();
            $table->string('category_name');
            $table->string('product_name');
            $table->string('sku');
            $table->string('target_no');
            $table->decimal('price', 15, 2);
            $table->string('payment_method');
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, expired, failed
            $table->string('topup_status')->default('pending'); // pending, processing, success, failed
            $table->text('note')->nullable(); // SN or error logs
            $table->json('payment_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
