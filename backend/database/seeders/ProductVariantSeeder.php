<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariantAttribute;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            // Lấy thuộc tính của danh mục qua category_attribute
            $attributes = Attribute::whereIn('id', function ($query) use ($product) {
                $query->select('attribute_id')
                    ->from('category_attribute')
                    ->where('category_id', $product->category_id);
            })->get();

            // Nếu không có thuộc tính, tạo biến thể mặc định
            if ($attributes->isEmpty()) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'SKU-' . $product->id . '-1',
                    'price' => rand(100000, 500000),
                    'stock' => rand(10, 50),
                    'status' => 'active',
                    'image_url' => 'https://via.placeholder.com/150',
                ]);
                continue;
            }

            // Lấy giá trị thuộc tính cho danh mục
            $attributeValues = [];
            foreach ($attributes as $attribute) {
                $values = AttributeValue::where('attribute_id', $attribute->id)
                    ->where('category_id', $product->category_id)
                    ->get();
                if ($values->isNotEmpty()) {
                    $attributeValues[$attribute->id] = $values->pluck('id')->toArray();
                }
            }

            // Nếu không có giá trị thuộc tính nào, tạo biến thể mặc định
            if (empty($attributeValues)) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'SKU-' . $product->id . '-1',
                    'price' => rand(100000, 500000),
                    'stock' => rand(10, 50),
                    'status' => 'active',
                    'image_url' => 'https://via.placeholder.com/150',
                ]);
                continue;
            }

            // Tạo tổ hợp biến thể
            $combinations = $this->generateCombinations($attributeValues);
            if (empty($combinations)) {
                // Nếu không tạo được tổ hợp, tạo biến thể mặc định
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'SKU-' . $product->id . '-1',
                    'price' => rand(100000, 500000),
                    'stock' => rand(10, 50),
                    'status' => 'active',
                    'image_url' => 'https://via.placeholder.com/150',
                ]);
                continue;
            }

            foreach ($combinations as $index => $combination) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'SKU-' . $product->id . '-' . ($index + 1),
                    'price' => rand(100000, 500000),
                    'stock' => rand(10, 50),
                    'status' => 'active',
                    'image_url' => 'https://via.placeholder.com/150',
                ]);

                foreach ($combination as $attributeId => $valueId) {
                    // Kiểm tra attribute_id và attribute_value_id hợp lệ
                    if ($attributeId > 0 && Attribute::where('id', $attributeId)->exists() && AttributeValue::where('id', $valueId)->exists()) {
                        ProductVariantAttribute::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attributeId,
                            'attribute_value_id' => $valueId,
                        ]);
                    } else {
                        \Log::warning('Invalid attribute or value skipped', [
                            'product_id' => $product->id,
                            'attribute_id' => $attributeId,
                            'attribute_value_id' => $valueId,
                        ]);
                    }
                }
            }
        }
    }

    private function generateCombinations($attributeValues)
    {
        if (empty($attributeValues)) {
            return [];
        }

        $result = [[]];
        foreach ($attributeValues as $attributeId => $values) {
            $newResult = [];
            foreach ($result as $combination) {
                foreach ($values as $value) {
                    $newResult[] = array_merge($combination, [$attributeId => $value]);
                }
            }
            $result = $newResult;
        }

        return $result;
    }
}