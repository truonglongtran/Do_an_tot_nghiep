<?php
namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                Log::warning('Shop not found for seller', ['seller_id' => $sellerId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $query = Product::where('shop_id', $shop->id)
                ->with([
                    'category' => fn($q) => $q->select('id', 'name'),
                    'variants' => fn($q) => $q->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status'),
                    'variants.variantAttributes' => fn($q) => $q->select(
                        'product_variant_attributes.id',
                        'product_variant_attributes.product_variant_id',
                        'attributes.name as attribute_name',
                        'attribute_values.value as attribute_value'
                    )
                    ->join('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                    ->join('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id')
                ]);

            if ($search = $request->input('search')) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('variants', function ($q) use ($search) {
                          $q->where('sku', 'like', "%{$search}%");
                      });
            }

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
            if ($stockMin = $request->input('stock_min')) {
                $query->whereHas('variants', fn($q) => $q->where('stock', '>=', $stockMin));
            }
            if ($stockMax = $request->input('stock_max')) {
                $query->whereHas('variants', fn($q) => $q->where('stock', '<=', $stockMax));
            }
            if ($priceMin = $request->input('price_min')) {
                $query->whereHas('variants', fn($q) => $q->where('price', '>=', $priceMin));
            }
            if ($priceMax = $request->input('price_max')) {
                $query->whereHas('variants', fn($q) => $q->where('price', '<=', $priceMax));
            }

            $products = $query->get()->map(function ($product) {
                $totalSales = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.product_id', $product->id)
                    ->where('orders.order_status', 'paid')
                    ->sum('order_items.quantity');

                $images = json_decode($product->images, true) ?? []; // Giải mã JSON

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                    'images' => array_map(fn($path) => Storage::url($path), $images),
                    'thumbnail' => (!empty($images)) ? Storage::url($images[0]) : ($product->variants->first()->image_url ? Storage::url($product->variants->first()->image_url) : null),
                    'price_min' => $product->variants->count() ? $product->variants->min('price') : $product->price,
                    'price_max' => $product->variants->count() ? $product->variants->max('price') : $product->price,
                    'total_stock' => $product->variants->count() ? $product->variants->sum('stock') : $product->stock,
                    'total_sales' => (int) $totalSales,
                    'status' => $product->status,
                    'variants' => $product->variants->map(function ($variant) {
                        $variantSales = DB::table('order_items')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('order_items.product_variant_id', $variant->id)
                            ->where('orders.order_status', 'paid')
                            ->sum('order_items.quantity');

                        $attributes = $variant->variantAttributes->reduce(function ($carry, $attr) {
                            if (strtolower($attr->attribute_name) === 'màu sắc') {
                                $carry['color'] = $attr->attribute_value;
                            } elseif (strtolower($attr->attribute_name) === 'kích cỡ') {
                                $carry['size'] = $attr->attribute_value;
                            }
                            $carry['attributes'][] = [
                                'name' => $attr->attribute_name,
                                'value' => $attr->attribute_value,
                            ];
                            return $carry;
                        }, ['color' => null, 'size' => null, 'attributes' => []]);

                        return [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'price' => $variant->price,
                            'stock' => $variant->stock,
                            'image_url' => $variant->image_url ? Storage::url($variant->image_url) : null,
                            'status' => $variant->status,
                            'sales' => (int) $variantSales,
                            'color' => $attributes['color'],
                            'size' => $attributes['size'],
                            'attributes' => $attributes['attributes'],
                        ];
                    }),
                ];
            });

            Log::info('Products fetched', [
                'seller_id' => $sellerId,
                'shop_id' => $shop->id,
                'products_count' => $products->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching products', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $product = Product::where('id', $id)
                ->where('shop_id', $shop->id)
                ->with([
                    'category' => fn($q) => $q->select('id', 'name'),
                    'variants' => fn($q) => $q->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status'),
                    'variants.variantAttributes' => fn($q) => $q->select(
                        'product_variant_attributes.id',
                        'product_variant_attributes.product_variant_id',
                        'product_variant_attributes.attribute_id',
                        'product_variant_attributes.attribute_value_id'
                    )
                ])
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm',
                ], 404);
            }

            $images = json_decode($product->images, true) ?? []; // Giải mã JSON

            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                'images' => array_map(fn($path) => Storage::url($path), $images),
                'status' => $product->status,
                'price' => $product->price,
                'stock' => $product->stock,
                'sku' => $product->variants->count() ? $product->variants->first()->sku : $product->sku,
                'image' => $product->variants->count() ? ($product->variants->first()->image_url ? Storage::url($product->variants->first()->image_url) : null) : (!empty($images) ? Storage::url($images[0]) : null),
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'stock' => $variant->stock,
                        'image_url' => $variant->image_url ? Storage::url($variant->image_url) : null,
                        'status' => $variant->status,
                        'attributes' => $variant->variantAttributes->map(function ($attr) {
                            return [
                                'attribute_id' => $attr->attribute_id,
                                'attribute_value_id' => $attr->attribute_value_id,
                            ];
                        }),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching product', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                Log::warning('Shop not found for seller', ['seller_id' => $sellerId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $hasAttributes = DB::table('category_attribute')
                ->where('category_id', $request->input('category_id'))
                ->exists();

            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|in:pending,approved,banned',
                'main_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ];

            if ($hasAttributes) {
                $rules = array_merge($rules, [
                    'variants' => 'required|array|min:1',
                    'variants.*.sku' => 'required|string|unique:product_variants,sku',
                    'variants.*.price' => 'required|numeric|min:0',
                    'variants.*.stock' => 'required|integer|min:0',
                    'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                    'variants.*.status' => 'required|in:active,inactive',
                    'variants.*.attributes' => 'required|array|min:1',
                    'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
                    'variants.*.attributes.*.attribute_value_id' => 'required|exists:attribute_values,id',
                ]);
            } else {
                $rules = array_merge($rules, [
                    'price' => 'required|numeric|min:0',
                    'stock' => 'required|integer|min:0',
                    'sku' => 'required|string|unique:product_variants,sku',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                ]);
            }

            $request->validate($rules);

            if ($hasAttributes) {
                foreach ($request->input('variants', []) as $index => $variant) {
                    foreach ($variant['attributes'] as $attr) {
                        $attributeValue = AttributeValue::where('id', $attr['attribute_value_id'])
                            ->where('category_id', $request->input('category_id'))
                            ->where('attribute_id', $attr['attribute_id'])
                            ->first();
                        if (!$attributeValue) {
                            throw ValidationException::withMessages([
                                "variants.{$index}.attributes" => "Giá trị thuộc tính {$attr['attribute_value_id']} không hợp lệ cho thuộc tính {$attr['attribute_id']} trong danh mục.",
                            ]);
                        }
                    }
                }
            }

            $product = null;
            $imageUrls = [];

            DB::transaction(function () use ($request, $shop, &$product, &$imageUrls, $hasAttributes) {
                $product = Product::create([
                    'shop_id' => $shop->id,
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'category_id' => $request->input('category_id'),
                    'images' => [], // Sẽ cập nhật sau
                    'status' => $request->input('status', 'pending'),
                    'price' => $hasAttributes ? null : $request->input('price'),
                    'stock' => $hasAttributes ? null : $request->input('stock'),
                ]);

                // Lưu ảnh chính
                $mainImagePath = $request->file('main_image')->storeAs(
                    "products/{$product->id}",
                    'main.jpg',
                    'public'
                );
                $imageUrls[] = $mainImagePath;

                // Lưu ảnh phụ
                if ($request->hasFile('additional_images')) {
                    foreach ($request->file('additional_images') as $index => $file) {
                        $additionalImagePath = $file->storeAs(
                            "products/{$product->id}/additional",
                            "img" . ($index + 1) . '.' . $file->getClientOriginalExtension(),
                            'public'
                        );
                        $imageUrls[] = $additionalImagePath;
                    }
                }

                // Cập nhật cột images
                $product->update(['images' => json_encode($imageUrls)]);

                if ($hasAttributes) {
                    $variants = $request->input('variants', []);
                    foreach ($variants as $index => $variant) {
                        $productVariant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $variant['sku'],
                            'price' => $variant['price'],
                            'stock' => $variant['stock'],
                            'image_url' => null, // Sẽ cập nhật sau
                            'status' => $variant['status'],
                        ]);

                        $imagePath = null;
                        if ($request->hasFile("variants.{$index}.image")) {
                            $imagePath = $request->file("variants.{$index}.image")->storeAs(
                                "product_variants/{$productVariant->id}",
                                'variant.jpg',
                                'public'
                            );
                            $productVariant->update(['image_url' => $imagePath]);
                        }

                        foreach ($variant['attributes'] as $attr) {
                            ProductVariantAttribute::create([
                                'product_variant_id' => $productVariant->id,
                                'attribute_id' => $attr['attribute_id'],
                                'attribute_value_id' => $attr['attribute_value_id'],
                            ]);
                        }
                    }
                } else {
                    $imagePath = null;
                    if ($request->hasFile('image')) {
                        $productVariant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $request->input('sku'),
                            'price' => $request->input('price'),
                            'stock' => $request->input('stock'),
                            'image_url' => null,
                            'status' => 'active',
                        ]);

                        $imagePath = $request->file('image')->storeAs(
                            "product_variants/{$productVariant->id}",
                            'variant.jpg',
                            'public'
                        );
                        $productVariant->update(['image_url' => $imagePath]);
                    } else {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $request->input('sku'),
                            'price' => $request->input('price'),
                            'stock' => $request->input('stock'),
                            'image_url' => null,
                            'status' => 'active',
                        ]);
                    }
                }
            });

            Log::info('Product created', [
                'seller_id' => $sellerId,
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'status' => $product->status,
                'image_urls' => $imageUrls,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm thành công',
                'data' => [
                    'product_id' => $product->id,
                    'images' => array_map(fn($path) => Storage::url($path), $imageUrls),
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating product', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $product = Product::where('id', $id)->where('shop_id', $shop->id)->first();
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm',
                ], 404);
            }

            $hasAttributes = DB::table('category_attribute')
                ->where('category_id', $request->input('category_id'))
                ->exists();

            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|in:pending,approved,banned',
                'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ];

            if ($hasAttributes) {
                $rules = array_merge($rules, [
                    'variants' => 'required|array|min:1',
                    'variants.*.id' => 'sometimes|exists:product_variants,id',
                    'variants.*.sku' => 'required|string|unique:product_variants,sku,' . ($request->input('variants.*.id') ?? 'NULL') . ',id',
                    'variants.*.price' => 'required|numeric|min:0',
                    'variants.*.stock' => 'required|integer|min:0',
                    'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                    'variants.*.status' => 'required|in:active,inactive',
                    'variants.*.attributes' => 'required|array|min:1',
                    'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
                    'variants.*.attributes.*.attribute_value_id' => 'required|exists:attribute_values,id',
                ]);
            } else {
                $rules = array_merge($rules, [
                    'price' => 'required|numeric|min:0',
                    'stock' => 'required|integer|min:0',
                    'sku' => 'required|string|unique:product_variants,sku,' . ($product->variants->first()->id ?? 'NULL') . ',id',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                ]);
            }

            $request->validate($rules);

            if ($hasAttributes) {
                foreach ($request->input('variants', []) as $index => $variant) {
                    foreach ($variant['attributes'] as $attr) {
                        $attributeValue = AttributeValue::where('id', $attr['attribute_value_id'])
                            ->where('category_id', $request->input('category_id'))
                            ->where('attribute_id', $attr['attribute_id'])
                            ->first();
                        if (!$attributeValue) {
                            throw ValidationException::withMessages([
                                "variants.{$index}.attributes" => "Giá trị thuộc tính {$attr['attribute_value_id']} không hợp lệ cho thuộc tính {$attr['attribute_id']} trong danh mục.",
                            ]);
                        }
                    }
                }
            }

            $imageUrls = json_decode($product->images, true) ?? [];

            DB::transaction(function () use ($request, $shop, &$product, &$imageUrls, $hasAttributes) {
                // Xóa ảnh cũ nếu có ảnh mới
                if ($request->hasFile('main_image') || $request->hasFile('additional_images')) {
                    Storage::disk('public')->deleteDirectory("products/{$product->id}");
                    $imageUrls = [];
                }

                // Lưu ảnh chính mới
                if ($request->hasFile('main_image')) {
                    $mainImagePath = $request->file('main_image')->storeAs(
                        "products/{$product->id}",
                        'main.jpg',
                        'public'
                    );
                    $imageUrls[0] = $mainImagePath;
                } elseif (count($imageUrls) > 0) {
                    $imageUrls[0] = $imageUrls[0]; // Giữ ảnh chính cũ
                }

                // Lưu ảnh phụ mới
                if ($request->hasFile('additional_images')) {
                    foreach ($request->file('additional_images') as $index => $file) {
                        $additionalImagePath = $file->storeAs(
                            "products/{$product->id}/additional",
                            "img" . ($index + 1) . '.' . $file->getClientOriginalExtension(),
                            'public'
                        );
                        $imageUrls[$index + 1] = $additionalImagePath;
                    }
                }

                // Cập nhật thông tin sản phẩm
                $product->update([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'category_id' => $request->input('category_id'),
                    'images' => json_encode(array_values($imageUrls)),
                    'status' => $request->input('status'),
                    'price' => $hasAttributes ? null : $request->input('price'),
                    'stock' => $hasAttributes ? null : $request->input('stock'),
                ]);

                if ($hasAttributes) {
                    $existingVariantIds = $product->variants()->pluck('id')->toArray();
                    $submittedVariantIds = collect($request->input('variants', []))->pluck('id')->filter()->toArray();

                    $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
                    foreach ($variantsToDelete as $variantId) {
                        Storage::disk('public')->deleteDirectory("product_variants/{$variantId}");
                        ProductVariant::where('id', $variantId)->delete();
                    }

                    foreach ($request->input('variants', []) as $index => $variant) {
                        $productVariant = ProductVariant::updateOrCreate(
                            ['id' => $variant['id'] ?? null, 'product_id' => $product->id],
                            [
                                'sku' => $variant['sku'],
                                'price' => $variant['price'],
                                'stock' => $variant['stock'],
                                'status' => $variant['status'],
                            ]
                        );

                        if ($request->hasFile("variants.{$index}.image")) {
                            Storage::disk('public')->deleteDirectory("product_variants/{$productVariant->id}");
                            $imagePath = $request->file("variants.{$index}.image")->storeAs(
                                "product_variants/{$productVariant->id}",
                                'variant.jpg',
                                'public'
                            );
                            $productVariant->update(['image_url' => $imagePath]);
                        }

                        ProductVariantAttribute::where('product_variant_id', $productVariant->id)->delete();
                        foreach ($variant['attributes'] as $attr) {
                            ProductVariantAttribute::create([
                                'product_variant_id' => $productVariant->id,
                                'attribute_id' => $attr['attribute_id'],
                                'attribute_value_id' => $attr['attribute_value_id'],
                            ]);
                        }
                    }
                } else {
                    $variant = $product->variants->first();
                    $imagePath = $variant ? $variant->image_url : null;
                    if ($request->hasFile('image')) {
                        if ($variant) {
                            Storage::disk('public')->deleteDirectory("product_variants/{$variant->id}");
                        }
                        $productVariant = ProductVariant::updateOrCreate(
                            ['id' => $variant ? $variant->id : null, 'product_id' => $product->id],
                            [
                                'sku' => $request->input('sku'),
                                'price' => $request->input('price'),
                                'stock' => $request->input('stock'),
                                'status' => 'active',
                            ]
                        );

                        $imagePath = $request->file('image')->storeAs(
                            "product_variants/{$productVariant->id}",
                            'variant.jpg',
                            'public'
                        );
                        $productVariant->update(['image_url' => $imagePath]);
                    } else {
                        if ($variant) {
                            $variant->update([
                                'sku' => $request->input('sku'),
                                'price' => $request->input('price'),
                                'stock' => $request->input('stock'),
                                'status' => 'active',
                            ]);
                        } else {
                            ProductVariant::create([
                                'product_id' => $product->id,
                                'sku' => $request->input('sku'),
                                'price' => $request->input('price'),
                                'stock' => $request->input('stock'),
                                'image_url' => null,
                                'status' => 'active',
                            ]);
                        }
                    }
                    ProductVariantAttribute::where('product_variant_id', $product->variants->first()->id)->delete();
                }
            });

            Log::info('Product updated', [
                'seller_id' => $sellerId,
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'status' => $product->status,
                'image_urls' => $imageUrls,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công',
                'data' => [
                    'product_id' => $product->id,
                    'images' => array_map(fn($path) => Storage::url($path), $imageUrls),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating product', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $sellerId = auth()->id();
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $product = Product::where('id', $id)->where('shop_id', $shop->id)->first();
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm',
                ], 404);
            }

            DB::transaction(function () use ($product) {
                Storage::disk('public')->deleteDirectory("products/{$product->id}");
                foreach ($product->variants as $variant) {
                    Storage::disk('public')->deleteDirectory("product_variants/{$variant->id}");
                }
                ProductVariantAttribute::whereIn('product_variant_id', $product->variants->pluck('id'))->delete();
                $product->variants()->delete();
                $product->delete();
            });

            Log::info('Product deleted', [
                'seller_id' => auth()->id(),
                'shop_id' => $shop->id,
                'product_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting product', [
                'seller_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateVariantStatus(Request $request, $variantId)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $variant = ProductVariant::where('id', $variantId)
                ->whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
                ->first();

            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy biến thể',
                ], 404);
            }

            $request->validate([
                'status' => 'required|in:active,inactive',
            ]);

            $variant->update([
                'status' => $request->input('status'),
            ]);

            Log::info('Variant status updated', [
                'seller_id' => $sellerId,
                'variant_id' => $variantId,
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái biến thể thành công',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating variant status', [
                'seller_id' => $sellerId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái biến thể: ' . $e->getMessage(),
            ], 500);
        }
    }
}