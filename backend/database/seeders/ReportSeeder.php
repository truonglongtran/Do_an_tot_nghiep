<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Tạo 15 bản ghi reports
        for ($i = 0; $i < 15; $i++) {
            $reportType = $faker->randomElement(['daily', 'week', 'month']);

            \App\Models\Report::create([
                'report_type' => $reportType,
                'file_url' => $faker->url() . "/reports/{$reportType}_" . $faker->uuid() . ".pdf",
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);
        }
    }
}