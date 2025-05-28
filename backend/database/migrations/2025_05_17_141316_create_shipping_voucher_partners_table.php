<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_voucher_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_voucher_id')->constrained('shipping_vouchers')->onDelete('cascade');
            $table->foreignId('shipping_partner_id')->constrained('shipping_partners');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_voucher_partners');
    }
};