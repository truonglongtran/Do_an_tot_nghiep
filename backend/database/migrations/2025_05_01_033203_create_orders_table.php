<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->decimal('total_amount', 10, 2);
            $table->boolean('settled_status');
            $table->timestamp('settled_at')->nullable();
            $table->enum('shipping_status', ['pending', 'processing', 'shipping', 'delivered', 'failed', 'returned']);
            $table->enum('order_status', ['pending', 'paid', 'canceled']);
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
