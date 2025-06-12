<?php
namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('user_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $query = Product::where('shop_id', $shop->id)
                ->with([
                    'category' => fn($q) => $q->select('id', 'name'),
                    'variants' => fn($q) => $q->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status'),
                    'variants.attributes' => fn($q) => $q->select(
                        'product_variant_attributes.id',
                        'product_variant_attributes.product_variant_id',
                        'attributes.name as attribute_name',
                        'attribute_values.value as attribute_value'
                    )
                    ->join('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                    ->join('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id')
                ]);

            // Tìm kiếm
            if ($search = $request->input('search')) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('variants', function ($q) use ($search) {
                          $q->where('sku', 'like', "%{$search}%");
                      });
            }

            // Lọc
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
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                    'images' => $product->images ?? [],
                    'thumbnail' => $product->images[0] ?? ($product->variants->first()->image_url ?? null),
                    'price_min' => $product->variants->min('price'),
                    'price_max' => $product->variants->max('price'),
                    'total_stock' => $product->variants->sum('stock'),
                    'status' => $product->status,
                    'variants' => $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'price' => $variant->price,
                            'stock' => $variant->stock,
                            'image_url' => $variant->image_url,
                            'status' => $variant->status,
                            'attributes' => $variant->attributes->map(fn($attr) => [
                                'name' => $attr->attribute_name,
                                'value' => $attr->attribute_value,
                            ]),
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
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('user_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'images' => 'required|array|min:1',
                'images.*' => 'file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'variants' => 'required|array|min:1',
                'variants.*.sku' => 'required|string|unique:product_variants,sku',
                'variants.*.price' => 'required|numeric|min:0',
                'variants.*.stock' => 'required|integer|min:0',
                'variants.*.image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'variants.*.attributes' => 'required|array',
                'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
                'variants.*.attributes.*.attribute_value_id' => 'required|exists:attribute_values,id',
                'shipping_partners' => 'array',
                'shipping_partners.*' => 'exists:shipping_partners,id',
            ]);

            $imageUrls = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageUrls[] = $image->store('products', 'public');
                }
            }

            $product = null;
            DB::transaction(function () use ($request, $shop, $imageUrls, &$product) {
                $product = Product::create([
                    'shop_id' => $shop->id,
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'category_id' => $request->input('category_id'),
                    'images' => json_encode($imageUrls),
                    'status' => 'pending',
                ]);

                $variants = $request->input('variants', []);
                foreach ($variants as $index => $variant) {
                    $imagePath = null;
                    if ($request->hasFile("variants.{$index}.image")) {
                        $imagePath = $request->file("variants.{$index}.image")->store('variants', 'public');
                    }

                    $productVariant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variant['sku'],
                        'price' => $variant['price'],
                        'stock' => $variant['stock'],
                        'image_url' => $imagePath,
                        'status' => 'active',
                    ]);

                    // Lưu thuộc tính động
                    foreach ($variant['attributes'] as $attr) {
                        ProductVariantAttribute::create([
                            'product_variant_id' => $productVariant->id,
                            'attribute_id' => $attr['attribute_id'],
                            'attribute_value_id' => $attr['attribute_value_id'],
                        ]);
                    }
                }

                // Gán đơn vị vận chuyển (nếu cần)
                if ($request->has('shipping_partners')) {
                    Log::info('Shipping partners assigned', [
                        'product_id' => $product->id,
                        'shipping_partners' => $request->input('shipping_partners'),
                    ]);
                    // TODO: Implement shipping partner assignment logic
                }
            });

            Log::info('Product created', [
                'seller_id' => $sellerId,
                'shop_id' => $shop->id,
                'product_id' => $product->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm thành công',
                'data' => [
                    'product_id' => $product->id,
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
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }
}