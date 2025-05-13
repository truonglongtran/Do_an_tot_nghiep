<?php

// database/migrations/xxxx_xx_xx_create_shop_shipping_partners_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopShippingPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_shipping_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade'); // Chỉ rõ tên bảng 'shops'
            $table->foreignId('shipping_partner_id')->constrained('shipping_partners')->onDelete('cascade'); // Chỉ rõ tên bảng 'shipping_partners'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_shipping_partners');
    }
}


