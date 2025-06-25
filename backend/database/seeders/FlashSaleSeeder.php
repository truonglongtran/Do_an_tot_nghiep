<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductVariant;
use App\Models\FlashSale;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FlashSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách product variants active
        $productVariants = ProductVariant::where('status', 'active')->get();

        if ($productVariants->isEmpty()) {
            Log::warning('No active product variants found for flash sale seeding.');
            return;
        }

        foreach ($productVariants as $variant) {
            // Ngẫu nhiên chọn 30% product variants để tạo flash sale
            if (rand(1, 100) <= 30) {
                $originalPrice = $variant->price;
                $discountPrice = $originalPrice * (1 - rand(10, 50) / 100); // Giảm 10-50%
                $startDate = Carbon::now()->addDays(rand(0, 5));
                $endDate = $startDate->copy()->addDays(rand(1, 7));

                FlashSale::create([
                    'product_variant_id' => $variant->id,
                    'discount_price' => $discountPrice,
                    'stock_limit' => rand(5, $variant->stock), // Giới hạn tồn kho nhỏ hơn hoặc bằng stock
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => rand(0, 1) ? 'active' : 'inactive',
                ]);

                Log::info('Created flash sale', [
                    'product_variant_id' => $variant->id,
                    'discount_price' => $discountPrice,
                    'stock_limit' => rand(5, $variant->stock),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
            }
        }
    }
}