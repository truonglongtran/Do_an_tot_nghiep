<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        ['name' => 'Chất liệu', 'type' => 'select'],
        ['name' => 'Phong cách', 'type' => 'select'],
        ['name' => 'Màn hình', 'type' => 'select'],
        ['name' => 'CPU', 'type' => 'select'],
        ['name' => 'Card đồ họa', 'type' => 'select'],
        ['name' => 'Loại đế', 'type' => 'select'],
    ];
    $categoryAssignments = [
        'Quần áo' => ['Màu sắc', 'Kích cỡ', 'Chất liệu', 'Phong cách'],
        'Điện thoại' => ['Màu sắc', 'Dung lượng', 'Màn hình', 'RAM'],
        'Laptop' => ['Màu sắc', 'RAM', 'Ổ cứng', 'CPU', 'Card đồ họa'],
        'Giày dép' => ['Màu sắc', 'Kích cỡ', 'Chất liệu', 'Loại đế'],
        'Sách' => [],
    ];

    // Create attributes
    foreach ($attributes as $attr) {
        Attribute::firstOrCreate(['name' => $attr['name']], $attr);
    }

    // Assign attributes to categories
    foreach ($categoryAssignments as $catName => $attrNames) {
        $category = Category::where('name', $catName)->first();
        if (!$category) {
            throw new \Exception("Category not found: $catName. Ensure CategorySeeder runs first.");
        }
        foreach ($attrNames as $attrName) {
            $attribute = Attribute::where('name', $attrName)->first();
            if (!$attribute) {
                throw new \Exception("Attribute not found: $attrName. Ensure attributes are seeded.");
            }
            DB::table('category_attribute')->updateOrInsert(
                [
                    'category_id' => $category->id,
                    'attribute_id' => $attribute->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
}
?>