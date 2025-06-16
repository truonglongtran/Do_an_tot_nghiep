<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariantAttribute;
use Illuminate\Support\Facades\Log;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with('category')->get();

        foreach ($products as $product) {
            if ($product->category && $product->category->name === 'Sách') {
                Log::info("Skipped variant creation for book product", [
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                ]);
                continue;
            }

            $attributes = Attribute::whereIn('id', function ($query) use ($product) {
                $query->select('attribute_id')
                    ->from('category_attribute')
                    ->where('category_id', $product->category_id);
            })->get();

            if ($attributes->isEmpty()) {
                Log::warning("No attributes found for category, creating default variant", [
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                ]);
                $this->createDefaultVariant($product);
                continue;
            }

            $attributeValues = [];

            // Fetch values for all attributes
            foreach ($attributes as $attribute) {
                $values = AttributeValue::where('attribute_id', $attribute->id)
                    ->where('category_id', $product->category_id)
                    ->pluck('id')
                    ->toArray();
                if (!empty($values)) {
                    $attributeValues[$attribute->id] = $values;
                }
            }

            if (empty($attributeValues)) {
                Log::warning("No attribute values found for category, creating default variant", [
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                ]);
                $this->createDefaultVariant($product);
                continue;
            }

            $combinations = $this->generateCombinations($attributeValues);
            Log::info("Generated combinations for product", [
                'product_id' => $product->id,
                'combination_count' => count($combinations),
            ]);

            if (empty($combinations)) {
                Log::warning("No combinations generated for product, creating default variant", [
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                ]);
                $this->createDefaultVariant($product);
                continue;
            }

            // Limit to 5 variants
            $combinations = array_slice($combinations, 0, 5);

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
                    if (Attribute::where('id', $attributeId)->exists() &&
                        AttributeValue::where('id', $valueId)
                            ->where('attribute_id', $attributeId)
                            ->where('category_id', $product->category_id)
                            ->exists()) {
                        ProductVariantAttribute::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attributeId,
                            'attribute_value_id' => $valueId,
                        ]);
                        $attribute = Attribute::find($attributeId);
                        $value = AttributeValue::find($valueId);
                        Log::info("Assigned attribute to variant", [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'attribute_name' => $attribute->name,
                            'value' => $value->value,
                        ]);
                    } else {
                        Log::warning('Invalid attribute or value skipped', [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'attribute_id' => $attributeId,
                            'attribute_value_id' => $valueId,
                        ]);
                    }
                }
            }
        }
    }

    private function createDefaultVariant($product)
    {
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-' . $product->id . '-1',
            'price' => rand(100000, 500000),
            'stock' => rand(10, 50),
            'status' => 'active',
            'image_url' => 'https://via.placeholder.com/150',
        ]);
    }

    private function generateCombinations($attributeValues)
    {
        if (empty($attributeValues)) {
            return [];
        }

        $result = [[]];
        foreach ($attributeValues as $attributeId => $values) {
            if (!is_numeric($attributeId) || $attributeId <= 0) {
                Log::warning("Invalid attribute ID in generateCombinations", [
                    'attribute_id' => $attributeId,
                ]);
                continue;
            }
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