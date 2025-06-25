<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('address_id')->constrained('buyer_addresses')->onDelete('restrict');
            $table->enum('settled_status', ['unsettled', 'settled'])->default('unsettled');
            $table->timestamp('settled_at')->nullable();
            $table->enum('shipping_status', ['pending', 'processing', 'shipping', 'delivered', 'failed', 'return'])->default('pending');
            $table->enum('order_status', ['pending', 'paid', 'canceled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2);
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->foreignId('shipping_voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->foreignId('shipping_partner_id')->nullable()->constrained('shipping_partners')->onDelete('set null');
            $table->string('tracking_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};