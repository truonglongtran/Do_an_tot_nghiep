<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Tạo 10 bản ghi banners
        for ($i = 0; $i < 10; $i++) {
            \App\Models\Banner::create([
                'title' => $faker->sentence(3),
                'img_url' => $faker->imageUrl(1200, 400),
                'link_url' => $faker->optional(0.8)->url(),
                'position' => $faker->randomElement(['homepage_top', 'category_page', 'product_page']),
                'start_date' => $faker->dateTimeBetween('-1 month', 'now'),
                'end_date' => $faker->dateTimeBetween('now', '+1 month'),
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}