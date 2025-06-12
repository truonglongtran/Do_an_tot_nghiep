<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            ['name' => 'Màu sắc', 'type' => 'select'],
            ['name' => 'Kích cỡ', 'type' => 'select'],
            ['name' => 'Dung lượng', 'type' => 'select'],
            ['name' => 'RAM', 'type' => 'select'],
            ['name' => 'Ổ cứng', 'type' => 'select'],
        ];

        $categoryAssignments = [
            'Quần áo' => ['Màu sắc', 'Kích cỡ'],
            'Điện thoại' => ['Màu sắc', 'Dung lượng'],
            'Laptop' => ['RAM', 'Ổ cứng'],
            'Giày dép' => ['Màu sắc', 'Kích cỡ'],
        ];

        // Tạo thuộc tính
        foreach ($attributes as $attr) {
            Attribute::create($attr);
        }

        // Gán thuộc tính cho danh mục
        foreach ($categoryAssignments as $catName => $attrNames) {
            $category = Category::where('name', $catName)->first();
            foreach ($attrNames as $attrName) {
                $attribute = Attribute::where('name', $attrName)->first();
                DB::table('category_attribute')->insert([
                    'category_id' => $category->id,
                    'attribute_id' => $attribute->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}