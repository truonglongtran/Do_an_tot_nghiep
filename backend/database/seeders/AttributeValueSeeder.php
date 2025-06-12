<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;

class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
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
                'RAM' => ['8GB', '16GB', '32GB'],
                'Ổ cứng' => ['256GB SSD', '512GB SSD', '1TB SSD'],
                'CPU' => ['i5', 'i7', 'M1'],
                'Card đồ họa' => ['Intel Iris', 'NVIDIA GTX 1650', 'M1 GPU'],
            ],
            'Giày dép' => [
                'Màu sắc' => ['Đen', 'Nâu', 'Trắng', 'Xám'],
                'Kích cỡ' => ['37', '38', '39', '40', '41', '42'],
                'Loại đế' => ['Đế bằng', 'Đế cao', 'Đế thể thao'],
                'Chất liệu' => ['Da', 'Vải', 'Cao su'],
            ],
            'Sách' => [
                'Ngôn ngữ' => ['Tiếng Việt', 'Tiếng Anh'],
                'Thể loại' => ['Tiểu thuyết', 'Kinh doanh', 'Khoa học', 'Tâm lý'],
                'Tác giả' => ['Nguyễn Nhật Ánh', 'J.K. Rowling', 'Dale Carnegie'],
            ],
        ];

        foreach ($values as $catName => $attrValues) {
            $category = Category::where('name', $catName)->first();
            foreach ($attrValues as $attrName => $valueList) {
                $attribute = Attribute::where('name', $attrName)->first();
                if (!$attribute || !$category) continue;

                foreach ($valueList as $val) {
                    AttributeValue::firstOrCreate([
                        'attribute_id' => $attribute->id,
                        'category_id' => $category->id,
                        'value' => $val,
                    ]);
                }
            }
        }
    }
}
