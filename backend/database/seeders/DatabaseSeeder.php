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
            // 1. Core Users and Admins (basic authentication)
            AdminSeeder::class,
            UserSeeder::class,

            // 2. Static / Reference Data (independent entities or minimal dependencies)
            CategorySeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            ShippingPartnerSeeder::class,
            ShopSeeder::class, // Shops depend on users (owner)

            // 3. Vouchers (crucial for orders, so they come before OrderSeeder)
            VoucherSeeder::class,
            PlatformVoucherSeeder::class,
            ShopVoucherSeeder::class,
            ShippingVoucherSeeder::class,
            ShippingVoucherPartnerSeeder::class,
            // ProductVoucherSeeder will be moved to run after products are seeded

            // 4. Products and their components (depend on categories, attributes, shops)
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductVoucherSeeder::class, // <-- Moved here: now runs after ProductSeeder

            // 5. User-specific addresses and shop follows (depend on users/shops)
            BuyerAddressSeeder::class,
            ShopFollowerSeeder::class,
            ShopShippingPartnerSeeder::class, // Depends on Shop and ShippingPartner

            // 6. Main Transactional Data (Orders & Order Items)
            // These depend on Users, Products, Vouchers, Shipping Partners, Buyer Addresses
            OrderSeeder::class,
            OrderItemSeeder::class,

            // 7. Dependent Transactional Data (Disputes, Reviews)
            // These depend on Orders, Users, Products
            DisputeSeeder::class,
            ReviewSeeder::class,

            // 8. Other Transactional / Related Data
            CartSeeder::class, // Depends on Users, Products/Variants
            FlashSaleSeeder::class, // Depends on Products
            SearchHistorySeeder::class, // Depends on Users, Products
            NotificationSeeder::class, // Can depend on Users
            MessageSeeder::class, // Depends on Users
            // ReportSeeder::class, // Can depend on various entities
            BannerSeeder::class, // General content
            // PaymentSeeder::class, // Uncomment if ready and dependencies met
            // AdminLogSeeder::class, // Uncomment if ready and dependencies met
        ]);
        
    }
}
