<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            ShippingPartnerSeeder::class,
            ShopSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            PaymentSeeder::class,
            DisputeSeeder::class,
            ReviewSeeder::class,
            VoucherSeeder::class,
            VoucherProductSeeder::class,
            BannerSeeder::class,
            AdminLogSeeder::class,
            ReportSeeder::class,
        ]);
       
    }
}