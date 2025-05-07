<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'shop_id' => 1,
                'name' => 'Áo thun basic',
                'description' => 'Áo thun cotton 100% thoáng mát',
                'price' => 150000,
                'stock' => 100,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shop_id' => 1,
                'name' => 'Quần jean ống đứng',
                'description' => 'Quần jean chất liệu cao cấp, bền đẹp',
                'price' => 350000,
                'stock' => 50,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shop_id' => 2,
                'name' => 'Váy hoa xòe',
                'description' => 'Váy hoa xinh xắn, phù hợp đi chơi, dạo phố',
                'price' => 250000,
                'stock' => 75,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shop_id' => 2,
                'name' => 'Giày sneaker thể thao',
                'description' => 'Giày sneaker năng động, êm ái',
                'price' => 400000,
                'stock' => 30,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shop_id' => 1,
                'name' => 'Mũ lưỡi trai',
                'description' => 'Mũ lưỡi trai thời trang, nhiều màu sắc',
                'price' => 80000,
                'stock' => 120,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

