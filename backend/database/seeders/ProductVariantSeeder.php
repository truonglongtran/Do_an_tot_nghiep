<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = DB::table('products')->pluck('id')->toArray();

        if (count($productIds) > 0) {
            $variants = [];
            foreach ($productIds as $productId) {
                for ($i = 0; $i < rand(2, 5); $i++) {
                    $color = ['Red', 'Blue', 'Green', 'Yellow', 'Black', 'White', 'Gray'][rand(0, 6)];
                    $size = ['S', 'M', 'L', 'XL', 'XXL'][rand(0, 4)];
                    $sku = Str::upper(Str::random(8));

                    $variants[] = [
                        'product_id' => $productId,
                        'color' => $color,
                        'size' => $size,
                        'sku' => $sku,
                        'price' => rand(50000, 1000000) / 100,
                        'stock' => rand(0, 200),
                        'image_url' => "https://example.com/variant_{$productId}_{$i}.jpg",
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