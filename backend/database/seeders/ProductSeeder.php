<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['shop_id' => 1, 'name' => 'Áo thun cổ tròn', 'description' => 'Áo thun cotton mềm mại', 'status' => 'approved'],
            ['shop_id' => 1, 'name' => 'Quần short kaki', 'description' => 'Quần short năng động', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Váy maxi dài', 'description' => 'Váy maxi dạo biển', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Giày bốt cổ thấp', 'description' => 'Giày bốt da cao cấp', 'status' => 'approved'],
            ['shop_id' => 1, 'name' => 'Áo khoác hoodie', 'description' => 'Áo khoác trẻ trung', 'status' => 'pending'],
            ['shop_id' => 1, 'name' => 'Quần jogger', 'description' => 'Quần jogger tập gym', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Túi xách mini', 'description' => 'Túi xách nhỏ gọn', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Mũ bucket', 'description' => 'Mũ bucket Hàn Quốc', 'status' => 'pending'],
            ['shop_id' => 1, 'name' => 'Áo sơ mi nam', 'description' => 'Áo sơ mi lụa', 'status' => 'approved'],
            ['shop_id' => 1, 'name' => 'Quần tây công sở', 'description' => 'Quần tây sang trọng', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Đồng hồ đeo tay', 'description' => 'Đồng hồ tinh tế', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Kính râm', 'description' => 'Kính râm chống UV', 'status' => 'approved'],
            ['shop_id' => 1, 'name' => 'Áo len cổ lọ', 'description' => 'Áo len mùa đông', 'status' => 'pending'],
            ['shop_id' => 1, 'name' => 'Ba lô du lịch', 'description' => 'Ba lô đa năng', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Chân váy bút chì', 'description' => 'Chân váy công sở', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Áo blazer nữ', 'description' => 'Áo blazer thanh lịch', 'status' => 'approved'],
            ['shop_id' => 1, 'name' => 'Dép quai hậu', 'description' => 'Dép quai hậu bền', 'status' => 'approved'],
            ['shop_id' => 1, 'name' => 'Thắt lưng da', 'description' => 'Thắt lưng tối giản', 'status' => 'approved'],
            ['shop_id' => 2, 'name' => 'Khăn choàng cổ', 'description' => 'Khăn choàng ấm', 'status' => 'pending'],
            ['shop_id' => 2, 'name' => 'Túi tote vải', 'description' => 'Túi tote thân thiện', 'status' => 'approved'],
        ];

        foreach ($products as $product) {
            $product['created_at'] = now();
            $product['updated_at'] = now();
            DB::table('products')->insert($product);
        }
    }
}