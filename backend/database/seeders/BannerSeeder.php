<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo 6 vị trí cố định
        $locations = [
            ['location_name' => 'Homepage Top', 'code' => 'HOME_TOP', 'description' => 'Banner đầu trang chính', 'location_type' => 'platform'],
            ['location_name' => 'Homepage Bottom', 'code' => 'HOME_BOTTOM', 'description' => 'Banner cuối trang chính', 'location_type' => 'platform'],
            ['location_name' => 'Campaign Banner', 'code' => 'CAMPAIGN_BANNER', 'description' => 'Banner chương trình khuyến mãi toàn hệ thống', 'location_type' => 'platform'],

            ['location_name' => 'Shop Top', 'code' => 'SHOP_TOP', 'description' => 'Banner đầu trang shop', 'location_type' => 'shop'],
            ['location_name' => 'Shop Bottom', 'code' => 'SHOP_BOTTOM', 'description' => 'Banner cuối trang shop', 'location_type' => 'shop'],
            ['location_name' => 'Shop Campaign', 'code' => 'SHOP_CAMPAIGN', 'description' => 'Banner chương trình khuyến mãi shop riêng', 'location_type' => 'shop'],
        ];

        $locationIds = [];
        foreach ($locations as $loc) {
            $locationIds[$loc['code']] = DB::table('banner_display_locations')->insertGetId($loc);
        }

        // 2. Tạo 50 banner mẫu
        $banners = [];
        for ($i = 1; $i <= 50; $i++) {
            $banners[] = [
                'title' => "Banner #$i",
                'img_url' => "https://example.com/banner$i.jpg",
                'link_url' => "https://example.com/promo$i",
                'start_date' => Carbon::now()->subDays(rand(0, 10)),
                'end_date' => Carbon::now()->addDays(rand(10, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $bannerIds = [];
        foreach ($banners as $banner) {
            $bannerIds[] = DB::table('banners')->insertGetId($banner);
        }

        // 3. Tạo placements
        // Logic: 
        // Banner 1-20: platform (shop_id = null), random location trong 3 platform locations
        // Banner 21-35: shop 1, random shop location
        // Banner 36-50: shop 2, random shop location

        $platformLocations = [
            $locationIds['HOME_TOP'],
            $locationIds['HOME_BOTTOM'],
            $locationIds['CAMPAIGN_BANNER'],
        ];

        $shopLocations = [
            $locationIds['SHOP_TOP'],
            $locationIds['SHOP_BOTTOM'],
            $locationIds['SHOP_CAMPAIGN'],
        ];

        // Tạo placements banner platform
        for ($i = 0; $i < 20; $i++) {
            $bannerId = $bannerIds[$i];
            $locationId = $platformLocations[array_rand($platformLocations)];
            DB::table('banner_placements')->insert([
                'banner_id' => $bannerId,
                'location_id' => $locationId,
                'shop_id' => null,
                'display_order' => rand(1, 5),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Banner shop 1
        for ($i = 20; $i < 35; $i++) {
            $bannerId = $bannerIds[$i];
            $locationId = $shopLocations[array_rand($shopLocations)];
            DB::table('banner_placements')->insert([
                'banner_id' => $bannerId,
                'location_id' => $locationId,
                'shop_id' => 1,
                'display_order' => rand(1, 5),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Banner shop 2
        for ($i = 35; $i < 50; $i++) {
            $bannerId = $bannerIds[$i];
            $locationId = $shopLocations[array_rand($shopLocations)];
            DB::table('banner_placements')->insert([
                'banner_id' => $bannerId,
                'location_id' => $locationId,
                'shop_id' => 2,
                'display_order' => rand(1, 5),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
