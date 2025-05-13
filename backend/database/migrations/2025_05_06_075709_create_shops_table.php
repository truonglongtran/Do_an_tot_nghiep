<?php

// database/migrations/xxxx_xx_xx_create_shops_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('shop_name');
            $table->enum('status', ['pending', 'active', 'banned'])->default('pending');
            $table->string('avatar_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('pickup_address');
            $table->string('ward');
            $table->string('district');
            $table->string('city');
            $table->string('phone_number');
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
        Schema::dropIfExists('shops');
    }
}


