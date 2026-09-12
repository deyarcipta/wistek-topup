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
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('title')->default('Flash Sale Special ⚡');
            $table->decimal('discount_price', 15, 2);
            $table->integer('stock_total')->default(20);
            $table->integer('stock_sold')->default(0);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_sales');
    }
};
