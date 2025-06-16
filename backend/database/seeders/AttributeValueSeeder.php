<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttributeValueSeeder extends Seeder
{
    // In Database\Seeders\AttributeValueSeeder.php
public function run(): void
{
    $validValues = [
        'Màu sắc' => ['Đỏ', 'Xanh Lá', 'Vàng', 'Đen', 'Trắng', 'Xanh Dương', 'Xanh Ngọc', 'Tím', 'Nâu', 'Xám'],
        'Kích cỡ' => ['S', 'M', 'L', 'XL', '37', '38', '39', '40', '41', '42'],
        'Chất liệu' => ['Cotton', 'Polyester', 'Denim', 'Da', 'Vải', 'Cao su'],
        'Phong cách' => ['Công sở', 'Thể thao', 'Đường phố'],
        'Dung lượng' => ['64GB', '128GB', '256GB', '512GB'],
        'RAM' => ['4GB', '6GB', '8GB', '16GB', '32GB'],
        'Màn hình' => ['5.5"', '6.1"', '6.7"'],
        'Ổ cứng' => ['256GB SSD', '512GB SSD', '1TB SSD'],
        'CPU' => ['i5', 'i7', 'M1'],
        'Card đồ họa' => ['Intel Iris', 'NVIDIA GTX 1650', 'M1 GPU'],
        'Loại đế' => ['Đế bằng', 'Đế cao', 'Đế thể thao'],
    ];

    $values = [
        'Quần áo' => [
            'Màu sắc' => ['Đỏ', 'Xanh Lá', 'Vàng', 'Đen', 'Trắng', 'Xanh Dương'],
            'Kích cỡ' => ['S', 'M', 'L', 'XL'],
            'Chất liệu' => ['Cotton', 'Polyester', 'Denim'],
            'Phong cách' => ['Công sở', 'Thể thao', 'Đường phố'],
        ],
        'Điện thoại' => [
            'Màu sắc' => ['Đen', 'Xanh Ngọc', 'Trắng', 'Tím'],
            'Dung lượng' => ['64GB', '128GB', '256GB', '512GB'],
            'RAM' => ['4GB', '6GB', '8GB'],
            'Màn hình' => ['5.5"', '6.1"', '6.7"'],
        ],
        'Laptop' => [
            'Màu sắc' => ['Đen', 'Xanh Ngọc', 'Trắng', 'Tím'],
            'RAM' => ['8GB', '16GB', '32GB'],
            'Ổ cứng' => ['256GB SSD', '512GB SSD', '1TB SSD'],
            'CPU' => ['i5', 'i7', 'M1'],
            'Card đồ họa' => ['Intel Iris', 'NVIDIA GTX 1650', 'M1 GPU'],
        ],
        'Giày dép' => [
            'Màu sắc' => ['Đen', 'Nâu', 'Trắng', 'Xám'],
            'Kích cỡ' => ['37', '38', '39', '40', '41', '42'],
            'Chất liệu' => ['Da', 'Vải', 'Cao su'],
            'Loại đế' => ['Đế bằng', 'Đế cao', 'Đế thể thao'],
        ],
    ];

    foreach ($values as $catName => $attrValues) {
        $category = Category::where('name', $catName)->first();
        if (!$category) {
            throw new \Exception("Category not found: $catName");
        }

        foreach ($attrValues as $attrName => $valueList) {
            $attribute = Attribute::where('name', $attrName)->first();
            if (!$attribute) {
                throw new \Exception("Attribute not found: $attrName");
            }

            $isAssigned = DB::table('category_attribute')
                ->where('category_id', $category->id)
                ->where('attribute_id', $attribute->id)
                ->exists();

            if (!$isAssigned) {
                throw new \Exception("Attribute $attrName is not assigned to category $catName");
            }

            foreach ($valueList as $val) {
                if (!in_array($val, $validValues[$attrName])) {
                    throw new \Exception("Invalid value '$val' for attribute '$attrName' in category '$catName'");
                }

                AttributeValue::updateOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'category_id' => $category->id,
                        'value' => $val,
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
}
?>