<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $shops = Shop::pluck('id')->toArray();

        if (empty($shops)) {
            throw new \Exception('No shops found. Please run ShopSeeder first.');
        }

        $bookCategory = $categories->where('name', 'Sách')->first();
        $phoneCategory = $categories->where('name', 'Điện thoại')->first();

        if (!$phoneCategory) {
            throw new \Exception('Category "Điện thoại" not found.');
        }

        foreach (range(1, 30) as $i) {
            $category = ($i <= 5 && $bookCategory) ? $bookCategory : ($i <= 10 ? $phoneCategory : $categories->random());
            $imageCount = rand(1, 3);
            $images = [];
            for ($j = 1; $j <= $imageCount; $j++) {
                $images[] = "https://via.placeholder.com/300?text=Product{$i}Image{$j}";
            }

            $isBook = $category->name === 'Sách';
            $data = [
                'name' => "Sản phẩm $i",
                'description' => "Mô tả sản phẩm $i",
                'category_id' => $category->id,
                'shop_id' => $shops[array_rand($shops)],
                'images' => json_encode($images),
                'status' => 'approved',
                'view_count' => rand(0, 1000), // Lượt xem ngẫu nhiên từ 0 đến 1000
                'sold_count' => rand(0, 200), // Số lượng bán ngẫu nhiên từ 0 đến 200
            ];

            if ($isBook) {
                $data['price'] = rand(50000, 200000);
                $data['stock'] = rand(10, 100);
            }

            Product::create($data);
        }
    }
}