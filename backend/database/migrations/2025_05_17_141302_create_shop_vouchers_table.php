<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shops');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_vouchers');
    }
};