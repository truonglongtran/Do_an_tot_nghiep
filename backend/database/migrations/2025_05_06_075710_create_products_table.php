<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['pending', 'approved', 'banned'])->default('pending');
            $table->decimal('price', 10, 2)->nullable(); // Price for products without variants
            $table->integer('stock')->nullable(); // Stock for products without variants
            $table->integer('view_count')->default(0); // Lượt xem sản phẩm
            $table->integer('sold_count')->default(0); // Số lượng sản phẩm đã bán
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};