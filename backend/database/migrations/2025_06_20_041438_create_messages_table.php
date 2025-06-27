<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->json('messages')->nullable(); // Lưu lịch sử tin nhắn dưới dạng JSON
            $table->unsignedInteger('unread_count')->default(0); // Số tin nhắn chưa đọc
            $table->timestamp('last_message_at')->nullable(); // Thời gian tin nhắn cuối
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};