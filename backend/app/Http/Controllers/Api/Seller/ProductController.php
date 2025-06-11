<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
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
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $query = Product::where('shop_id', $shop->id)
                ->with(['variants' => function ($q) {
                    $q->select('product_id', 'color', 'size', 'sku', 'price', 'stock', 'image_url', 'status');
                }]);

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
                $query->whereHas('variants', function ($q) use ($stockMin) {
                    $q->where('stock', '>=', $stockMin);
                });
            }
            if ($stockMax = $request->input('stock_max')) {
                $query->whereHas('variants', function ($q) use ($stockMax) {
                    $q->where('stock', '<=', $stockMax);
                });
            }
            if ($priceMin = $request->input('price_min')) {
                $query->whereHas('variants', function ($q) use ($priceMin) {
                    $q->where('price', '>=', $priceMin);
                });
            }
            if ($priceMax = $request->input('price_max')) {
                $query->whereHas('variants', function ($q) use ($priceMax) {
                    $q->where('price', '<=', $priceMax);
                });
            }

            $products = $query->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'thumbnail' => $product->variants->first()->image_url ?? null,
                    'price_min' => $product->variants->min('price'),
                    'price_max' => $product->variants->max('price'),
                    'total_stock' => $product->variants->sum('stock'),
                    'status' => $product->status,
                    'variants' => $product->variants,
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
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'variants.*.color' => 'nullable|string',
                'variants.*.size' => 'nullable|string',
                'variants.*.sku' => 'required|string|unique:product_variants,sku',
                'variants.*.price' => 'required|numeric|min:0',
                'variants.*.stock' => 'required|integer|min:0',
                'variants.*.image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'shipping_partners' => 'array',
                'shipping_partners.*' => 'exists:shipping_partners,id',
            ]);

            DB::transaction(function () use ($request, $shop) {
                $product = Product::create([
                    'shop_id' => $shop->id,
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'status' => 'pending',
                ]);

                $variants = $request->input('variants', []);
                foreach ($variants as $variant) {
                    $imagePath = null;
                    if ($request->hasFile("variants.{$variant['index']}.image")) {
                        $imagePath = $request->file("variants.{$variant['index']}.image")->store('variants', 'public');
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'color' => $variant['color'] ?? null,
                        'size' => $variant['size'] ?? null,
                        'sku' => $variant['sku'],
                        'price' => $variant['price'],
                        'stock' => $variant['stock'],
                        'image_url' => $imagePath,
                        'status' => 'active',
                    ]);
                }

                // Gán đơn vị vận chuyển (nếu cần)
                if ($request->has('shipping_partners')) {
                    // Logic gán đơn vị vận chuyển cho sản phẩm (chưa triển khai chi tiết)
                    Log::info('Shipping partners assigned', [
                        'product_id' => $product->id,
                        'shipping_partners' => $request->input('shipping_partners'),
                    ]);
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
            ]);
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