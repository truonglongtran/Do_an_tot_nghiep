<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $buyer1 = User::where('email', 'buyer1@example.com')->first();
        $buyer2 = User::where('email', 'buyer2@example.com')->first();
        $seller1 = User::where('email', 'seller1@example.com')->first();
        $seller2 = User::where('email', 'seller2@example.com')->first();

        // Hội thoại 1: Giữa buyer1 và seller1 (hỏi về sản phẩm, thương lượng giá)
        Message::create([
            'buyer_id' => $buyer1->id,
            'seller_id' => $seller1->id,
            'messages' => [
                [
                    'sender_type' => 'buyer',
                    'content' => 'Chào anh, sản phẩm áo thun nam size M còn hàng không?',
                    'created_at' => now()->toIso8601String(),
                    'is_read' => false,
                ],
                [
                    'sender_type' => 'seller',
                    'content' => 'Chào bạn, áo thun size M vẫn còn hàng. Bạn muốn màu nào? Có trắng, đen và xanh navy.',
                    'created_at' => now()->addMinutes(5)->toIso8601String(),
                    'is_read' => true,
                ],
                [
                    'sender_type' => 'buyer',
                    'content' => 'Mình muốn màu đen. Giá bao nhiêu vậy anh? Có giảm giá nếu mua 2 cái không?',
                    'created_at' => now()->addMinutes(10)->toIso8601String(),
                    'is_read' => true,
                ],
                [
                    'sender_type' => 'seller',
                    'content' => 'Áo màu đen giá 150k/cái. Nếu mua 2 cái, mình giảm còn 280k nhé. Bạn muốn đặt luôn không?',
                    'created_at' => now()->addMinutes(15)->toIso8601String(),
                    'is_read' => true,
                ],
                [
                    'sender_type' => 'buyer',
                    'content' => 'Ok, mình lấy 2 cái màu đen. Shop có giao hàng COD không?',
                    'created_at' => now()->addMinutes(20)->toIso8601String(),
                    'is_read' => false,
                ],
                [
                    'sender_type' => 'seller',
                    'content' => 'Có COD nhé bạn. Bạn cho mình xin địa chỉ giao hàng và số điện thoại để mình chuẩn bị đơn.',
                    'created_at' => now()->addMinutes(25)->toIso8601String(),
                    'is_read' => false,
                ],
            ],
            'unread_count' => 2, // 2 tin nhắn chưa đọc từ buyer
            'last_message_at' => now()->addMinutes(25),
            'created_at' => now(),
            'updated_at' => now()->addMinutes(25),
        ]);

        // Hội thoại 2: Giữa buyer2 và seller1 (hỏi về giao hàng và voucher)
        Message::create([
            'buyer_id' => $buyer2->id,
            'seller_id' => $seller1->id,
            'messages' => [
                [
                    'sender_type' => 'buyer',
                    'content' => 'Chào shop, mình muốn mua giày sneaker trắng. Shop có hỗ trợ giao hàng nhanh không?',
                    'created_at' => now()->addMinutes(30)->toIso8601String(),
                    'is_read' => false,
                ],
                [
                    'sender_type' => 'seller',
                    'content' => 'Chào bạn, giày sneaker trắng còn hàng. Giao hàng nhanh thì phụ thuộc vào khu vực, bạn ở đâu vậy? Mình có hỗ trợ giao trong ngày tại TP.HCM.',
                    'created_at' => now()->addMinutes(35)->toIso8601String(),
                    'is_read' => true,
                ],
                [
                    'sender_type' => 'buyer',
                    'content' => 'Mình ở Q.7, TP.HCM. Có voucher giảm giá nào không shop?',
                    'created_at' => now()->addMinutes(40)->toIso8601String(),
                    'is_read' => true,
                ],
                [
                    'sender_type' => 'seller',
                    'content' => 'Ở Q.7 thì giao trong ngày được bạn. Mình có voucher giảm 10% cho đơn từ 500k, bạn áp mã VOUCHER10 khi đặt nhé.',
                    'created_at' => now()->addMinutes(45)->toIso8601String(),
                    'is_read' => false,
                ],
            ],
            'unread_count' => 1, // 1 tin nhắn chưa đọc từ buyer
            'last_message_at' => now()->addMinutes(45),
            'created_at' => now(),
            'updated_at' => now()->addMinutes(45),
        ]);

        // Hội thoại 3: Giữa buyer1 và seller2 (hỏi về chính sách bảo hành và đổi trả)
        Message::create([
            'buyer_id' => $buyer1->id,
            'seller_id' => $seller2->id,
            'messages' => [
                [
                    'sender_type' => 'buyer',
                    'content' => 'Chào shop, tôi muốn hỏi về chính sách bảo hành cho tai nghe Bluetooth.',
                    'created_at' => now()->addMinutes(50)->toIso8601String(),
                    'is_read' => false,
                ],
                [
                    'sender_type' => 'seller',
                    'content' => 'Chào bạn, tai nghe Bluetooth bên mình bảo hành 6 tháng, 1 đổi 1 nếu lỗi nhà sản xuất. Bạn cần thêm thông tin gì không?',
                    'created_at' => now()->addMinutes(55)->toIso8601String(),
                    'is_read' => true,
                ],
                [
                    'sender_type' => 'buyer',
                    'content' => 'Nếu tai nghe bị lỗi thì mình gửi lại shop kiểu gì? Có mất phí không?',
                    'created_at' => now()->addMinutes(60)->toIso8601String(),
                    'is_read' => false,
                ],
                [
                    'sender_type' => 'seller',
                    'content' => 'Bạn gửi qua bưu điện về địa chỉ shop, phí gửi mình hỗ trợ 100%. Nhớ đóng gói cẩn thận và gửi kèm hóa đơn nhé!',
                    'created_at' => now()->addMinutes(65)->toIso8601String(),
                    'is_read' => false,
                ],
            ],
            'unread_count' => 2, // 2 tin nhắn chưa đọc từ buyer
            'last_message_at' => now()->addMinutes(65),
            'created_at' => now(),
            'updated_at' => now()->addMinutes(65),
        ]);
    }
}