<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('img_url', 255); // Keep non-nullable as per original
            $table->string('link_url', 255)->nullable(); // Allow link_url to be nullable
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
        });

        Schema::create('banner_display_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name', 255)->unique();
            $table->string('code', 255)->unique();
            $table->text('description')->nullable();
            $table->enum('location_type', ['platform', 'shop']);
        });

        Schema::create('banner_placements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banner_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['banner_id', 'location_id', 'shop_id']);

            $table->foreign('banner_id')->references('id')->on('banners')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('banner_display_locations')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_placements');
        Schema::dropIfExists('banner_display_locations');
        Schema::dropIfExists('banners');
    }
};