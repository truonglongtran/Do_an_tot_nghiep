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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->decimal('total_amount', 10, 2);
            $table->enum('settled_status', ['unsettled', 'settled'])->default('unsettled');
            $table->timestamp('settled_at')->nullable();
            $table->enum('shipping_status', ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'])->default('pending');
            $table->enum('order_status', ['pending', 'paid', 'canceled'])->default('pending');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
