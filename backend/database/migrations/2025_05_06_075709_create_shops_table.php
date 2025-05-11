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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('shop_name');
            $table->string('pickup_address');
            $table->string('ward');
            $table->string('district');
            $table->string('city');
            $table->string('phone_number');
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['pending', 'active', 'banned'])->default('pending');
            $table->string('avatar_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
