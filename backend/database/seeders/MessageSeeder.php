<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyer1 = User::where('email', 'buyer1@example.com')->first();
        $buyer2 = User::where('email', 'buyer2@example.com')->first();
        $seller1 = User::where('email', 'seller1@example.com')->first();
        $seller2 = User::where('email', 'seller2@example.com')->first();

        // Hội thoại 1: Giữa buyer1 và seller1 (hỏi về sản phẩm, thương lượng giá)
        Message::create([
            'sender_id' => $buyer1->id,
            'receiver_id' => $seller1->id,
            'content' => 'Chào anh, sản phẩm áo thun nam size M còn hàng không?',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Message::create([
            'sender_id' => $seller1->id,
            'receiver_id' => $buyer1->id,
            'content' => 'Chào bạn, áo thun size M vẫn còn hàng. Bạn muốn màu nào? Có trắng, đen và xanh navy.',
            'is_read' => true,
            'created_at' => now()->addMinutes(5),
            'updated_at' => now()->addMinutes(5),
        ]);

        Message::create([
            'sender_id' => $buyer1->id,
            'receiver_id' => $seller1->id,
            'content' => 'Mình muốn màu đen. Giá bao nhiêu vậy anh? Có giảm giá nếu mua 2 cái không?',
            'is_read' => true,
            'created_at' => now()->addMinutes(10),
            'updated_at' => now()->addMinutes(10),
        ]);

        Message::create([
            'sender_id' => $seller1->id,
            'receiver_id' => $buyer1->id,
            'content' => 'Áo màu đen giá 150k/cái. Nếu mua 2 cái, mình giảm còn 280k nhé. Bạn muốn đặt luôn không?',
            'is_read' => true,
            'created_at' => now()->addMinutes(15),
            'updated_at' => now()->addMinutes(15),
        ]);

        Message::create([
            'sender_id' => $buyer1->id,
            'receiver_id' => $seller1->id,
            'content' => 'Ok, mình lấy 2 cái màu đen. Shop có giao hàng COD không?',
            'is_read' => false,
            'created_at' => now()->addMinutes(20),
            'updated_at' => now()->addMinutes(20),
        ]);

        Message::create([
            'sender_id' => $seller1->id,
            'receiver_id' => $buyer1->id,
            'content' => 'Có COD nhé bạn. Bạn cho mình xin địa chỉ giao hàng và số điện thoại để mình chuẩn bị đơn.',
            'is_read' => false,
            'created_at' => now()->addMinutes(25),
            'updated_at' => now()->addMinutes(25),
        ]);

        // Hội thoại 2: Giữa buyer2 và seller1 (hỏi về giao hàng và voucher)
        Message::create([
            'sender_id' => $buyer2->id,
            'receiver_id' => $seller1->id,
            'content' => 'Chào shop, mình muốn mua giày sneaker trắng. Shop có hỗ trợ giao hàng nhanh không?',
            'is_read' => false,
            'created_at' => now()->addMinutes(30),
            'updated_at' => now()->addMinutes(30),
        ]);

        Message::create([
            'sender_id' => $seller1->id,
            'receiver_id' => $buyer2->id,
            'content' => 'Chào bạn, giày sneaker trắng còn hàng. Giao hàng nhanh thì phụ thuộc vào khu vực, bạn ở đâu vậy? Mình có hỗ trợ giao trong ngày tại TP.HCM.',
            'is_read' => true,
            'created_at' => now()->addMinutes(35),
            'updated_at' => now()->addMinutes(35),
        ]);

        Message::create([
            'sender_id' => $buyer2->id,
            'receiver_id' => $seller1->id,
            'content' => 'Mình ở Q.7, TP.HCM. Có voucher giảm giá nào không shop?',
            'is_read' => true,
            'created_at' => now()->addMinutes(40),
            'updated_at' => now()->addMinutes(40),
        ]);

        Message::create([
            'sender_id' => $seller1->id,
            'receiver_id' => $buyer2->id,
            'content' => 'Ở Q.7 thì giao trong ngày được bạn. Mình có voucher giảm 10% cho đơn từ 500k, bạn áp mã VOUCHER10 khi đặt nhé.',
            'is_read' => false,
            'created_at' => now()->addMinutes(45),
            'updated_at' => now()->addMinutes(45),
        ]);

        // Hội thoại 3: Giữa buyer1 và seller2 (hỏi về chính sách bảo hành và đổi trả)
        Message::create([
            'sender_id' => $buyer1->id,
            'receiver_id' => $seller2->id,
            'content' => 'Chào shop, tôi muốn hỏi về chính sách bảo hành cho tai nghe Bluetooth.',
            'is_read' => false,
            'created_at' => now()->addMinutes(50),
            'updated_at' => now()->addMinutes(50),
        ]);

        Message::create([
            'sender_id' => $seller2->id,
            'receiver_id' => $buyer1->id,
            'content' => 'Chào bạn, tai nghe Bluetooth bên mình bảo hành 6 tháng, 1 đổi 1 nếu lỗi nhà sản xuất. Bạn cần thêm thông tin gì không?',
            'is_read' => true,
            'created_at' => now()->addMinutes(55),
            'updated_at' => now()->addMinutes(55),
        ]);

        Message::create([
            'sender_id' => $buyer1->id,
            'receiver_id' => $seller2->id,
            'content' => 'Nếu tai nghe bị lỗi thì mình gửi lại shop kiểu gì? Có mất phí không?',
            'is_read' => false,
            'created_at' => now()->addMinutes(60),
            'updated_at' => now()->addMinutes(60),
        ]);

        Message::create([
            'sender_id' => $seller2->id,
            'receiver_id' => $buyer1->id,
            'content' => 'Bạn gửi qua bưu điện về địa chỉ shop, phí gửi mình hỗ trợ 100%. Nhớ đóng gói cẩn thận và gửi kèm hóa đơn nhé!',
            'is_read' => false,
            'created_at' => now()->addMinutes(65),
            'updated_at' => now()->addMinutes(65),
        ]);
    }
}