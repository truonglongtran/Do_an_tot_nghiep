<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách người mua (buyers)
        $buyers = User::where('role', 'buyer')->where('status', 'active')->get();

        if ($buyers->isEmpty()) {
            Log::warning('No active buyers found for cart seeding.');
            return;
        }

        // Lấy danh sách product variants
        $productVariants = ProductVariant::where('status', 'active')->get();

        if ($productVariants->isEmpty()) {
            Log::warning('No active product variants found for cart seeding.');
            return;
        }

        foreach ($buyers as $buyer) {
            // Mỗi người mua sẽ có từ 1-3 sản phẩm trong giỏ hàng
            $randomVariants = $productVariants->random(rand(1, 3));

            foreach ($randomVariants as $variant) {
                Cart::create([
                    'user_id' => $buyer->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => rand(1, 5),
                ]);

                Log::info('Created cart item', [
                    'user_id' => $buyer->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => rand(1, 5),
                ]);
            }
        }
    }
}