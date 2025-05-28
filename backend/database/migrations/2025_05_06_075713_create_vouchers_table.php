<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->enum('voucher_type', ['platform', 'shop', 'shipping', 'product']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};