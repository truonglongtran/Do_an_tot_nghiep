<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach (range(1, 30) as $i) {
            $category = $categories->random();
            $imageCount = rand(1, 3);
            $images = [];
            for ($j = 1; $j <= $imageCount; $j++) {
                $images[] = "https://via.placeholder.com/300?text=Product{$i}Image{$j}";
            }

            Product::create([
                'name' => "Sản phẩm $i",
                'description' => "Mô tả sản phẩm $i",
                'category_id' => $category->id,
                'shop_id' => $i % 2 === 0 ? 2 : 1, 
                'images' => json_encode($images), 
                'status' => 'approved',
            ]);
        }
    }
}