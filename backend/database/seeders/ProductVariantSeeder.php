<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách product_id từ bảng products
        $productIds = DB::table('products')->pluck('id')->toArray();

        // Kiểm tra xem có sản phẩm nào không
        if (count($productIds) > 0) {
            $variants = [];
            foreach ($productIds as $productId) {
                // Tạo các biến thể cho mỗi sản phẩm.  Số lượng biến thể có thể khác.
                for ($i = 0; $i < rand(1, 3); $i++) { // Random từ 1 đến 3 biến thể cho mỗi sản phẩm
                    $color = ['Red', 'Blue', 'Green', 'Yellow', 'Black'][rand(0, 4)];
                    $size = ['S', 'M', 'L', 'XL'][rand(0, 3)];
                    $sku = Str::upper(Str::random(8)); // Tạo SKU ngẫu nhiên

                    $variants[] = [
                        'product_id' => $productId,
                        'color' => $color,
                        'size' => $size,
                        'sku' => $sku,
                        'price' => rand(100000, 1000000), // Giá từ 100,000 đến 1,000,000
                        'stock' => rand(0, 100),           // Tồn kho từ 0 đến 100
                        'image_url' => "https://example.com/variant_{$productId}_{$i}.jpg", // URL ảnh
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            DB::table('product_variants')->insert($variants);
        }
    }
}