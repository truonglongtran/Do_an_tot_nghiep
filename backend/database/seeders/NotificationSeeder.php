<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Notifications cho buyer1 (user_id = 1)
        Notification::create([
            'user_id' => 1,
            'type' => 'order',
            'title' => 'Cập nhật đơn hàng',
            'message' => 'Đơn hàng #1001 đang được giao đến địa chỉ của bạn.',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Notification::create([
            'user_id' => 1,
            'type' => 'promotion',
            'title' => 'Khuyến mãi mới',
            'message' => 'Voucher CODE123 giảm 20% cho đơn từ 100k, hết hạn 20/06/2025.',
            'is_read' => true,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Notification::create([
            'user_id' => 1,
            'type' => 'system',
            'title' => 'Cập nhật ứng dụng',
            'message' => 'Phiên bản mới đã có, vui lòng cập nhật để trải nghiệm tốt hơn.',
            'is_read' => false,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        // Notifications cho buyer2 (user_id = 2)
        Notification::create([
            'user_id' => 2,
            'type' => 'order',
            'title' => 'Đơn hàng đã hoàn thành',
            'message' => 'Đơn hàng #1003 đã được giao thành công. Vui lòng đánh giá sản phẩm!',
            'is_read' => false,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        Notification::create([
            'user_id' => 2,
            'type' => 'promotion',
            'title' => 'Flash Sale sắp tới',
            'message' => 'Flash Sale 18/06/2025, giảm đến 50% cho các sản phẩm thời trang!',
            'is_read' => false,
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        Notification::create([
            'user_id' => 2,
            'type' => 'order',
            'title' => 'Hủy đơn hàng',
            'message' => 'Đơn hàng #1004 đã bị hủy do hết hàng. Hoàn tiền sẽ được xử lý trong 3-5 ngày.',
            'is_read' => true,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        // Notifications cho seller1 (user_id = 3, cũng có thể là buyer)
        Notification::create([
            'user_id' => 3,
            'type' => 'order',
            'title' => 'Đơn hàng mới',
            'message' => 'Bạn có đơn hàng #1002 cần xử lý. Vui lòng xác nhận trước 19/06/2025.',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Notification::create([
            'user_id' => 3,
            'type' => 'promotion',
            'title' => 'Tăng doanh số với voucher',
            'message' => 'Tạo voucher giảm giá cho cửa hàng của bạn để thu hút khách hàng!',
            'is_read' => false,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        Notification::create([
            'user_id' => 3,
            'type' => 'system',
            'title' => 'Thông báo bảo trì',
            'message' => 'Hệ thống sẽ bảo trì từ 2:00 AM đến 4:00 AM ngày 18/06/2025.',
            'is_read' => false,
            'created_at' => now()->subHours(10),
            'updated_at' => now()->subHours(10),
        ]);

        Notification::create([
            'user_id' => 3,
            'type' => 'order',
            'title' => 'Đặt hàng thành công',
            'message' => 'Bạn đã đặt đơn hàng #1005 thành công với tư cách người mua.',
            'is_read' => true,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // Notifications cho seller2 (user_id = 4, cũng có thể là buyer)
        Notification::create([
            'user_id' => 4,
            'type' => 'system',
            'title' => 'Tài khoản bị cấm',
            'message' => 'Cửa hàng của bạn đã bị cấm do vi phạm chính sách. Liên hệ hỗ trợ để biết thêm chi tiết.',
            'is_read' => false,
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(7),
        ]);

        Notification::create([
            'user_id' => 4,
            'type' => 'order',
            'title' => 'Đơn hàng cần xử lý',
            'message' => 'Đơn hàng #1006 đang chờ xác nhận. Vui lòng xử lý trước khi tài khoản bị cấm.',
            'is_read' => true,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        Notification::create([
            'user_id' => 4,
            'type' => 'promotion',
            'title' => 'Khuyến mãi trước khi cấm',
            'message' => 'Sử dụng voucher CODE456 để giảm 10% trước khi tài khoản bị khóa.',
            'is_read' => false,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        Notification::create([
            'user_id' => 4,
            'type' => 'order',
            'title' => 'Mua hàng thành công',
            'message' => 'Bạn đã đặt đơn hàng #1007 thành công với tư cách người mua.',
            'is_read' => false,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);
    }
}