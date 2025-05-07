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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('usage_limited')->nullable();
            $table->integer('used_count')->default(0);
            $table->enum('voucher_type', ['platform', 'shop', 'shipping', 'product']);
            $table->foreignId('shop_id')->nullable()->constrained('shops');
            $table->boolean('shipping_only')->default(false);
            $table->json('applicable_shipping_partners')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
