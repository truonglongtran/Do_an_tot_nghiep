<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Quần áo', 'slug' => 'quan-ao', 'description' => 'Trang phục các loại'],
            ['name' => 'Sách', 'slug' => 'sach', 'description' => 'Sách giáo khoa, truyện, tiểu thuyết'],
            ['name' => 'Điện thoại', 'slug' => 'dien-thoai', 'description' => 'Điện thoại di động'],
            ['name' => 'Laptop', 'slug' => 'laptop', 'description' => 'Máy tính xách tay'],
            ['name' => 'Giày dép', 'slug' => 'giay-dep', 'description' => 'Giày, dép các loại'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}