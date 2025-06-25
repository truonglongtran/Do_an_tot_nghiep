<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\Log;

class SearchHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách từ khóa mẫu
        $keywords = [
            'laptop', 'smartphone', 'headphones', 'smartwatch', 'tablet',
            'gaming mouse', 'keyboard', 'monitor', 'camera', 'speaker',
            'charger', 'backpack', 'earbuds', 'printer', 'router',
        ];

        // Lấy danh sách người dùng active
        $activeUsers = User::where('status', 'active')->get();

        if ($activeUsers->isEmpty()) {
            Log::warning('No active users found for search history seeding.');
            return;
        }

        foreach ($activeUsers as $user) {
            // Mỗi người dùng có 1-5 từ khóa tìm kiếm ngẫu nhiên
            $randomKeywords = array_rand(array_flip($keywords), rand(1, 5));

            foreach ((array) $randomKeywords as $keyword) {
                try {
                    SearchHistory::create([
                        'user_id' => $user->id,
                        'keyword' => $keyword,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('Created search history', [
                        'user_id' => $user->id,
                        'keyword' => $keyword,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create search history', [
                        'user_id' => $user->id,
                        'keyword' => $keyword,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}