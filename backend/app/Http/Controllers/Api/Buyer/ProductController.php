<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        try {
            $product = Product::where('id', $id)
                ->whereNull('deleted_at')
                ->with([
                    'variants' => function ($query) {
                        $query->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status')
                            ->where('status', 'active');
                    },
                    'shop' => function ($query) {
                        $query->select('id', 'shop_name');
                    },
                    'reviews.user' => function ($query) {
                        $query->select('id', 'username');
                    }
                ])
                ->select('id', 'name', 'description', 'shop_id', 'sold_count', 'images', 'price')
                ->firstOrFail();

            if (!$product->shop) {
                throw new \Exception('Cửa hàng không hợp lệ');
            }

            $images = is_array($product->images) ? $product->images : (json_decode($product->images, true) ?? []);

            return response()->json([
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name ?? '',
                    'description' => $product->description ?? '',
                    'sold_count' => $product->sold_count ?? 0,
                    'price' => $product->price, // Giá từ products
                    'lowest_price' => $product->variants->count() > 0 ? $product->variants->min('price') : $product->price,
                    'images' => $images,
                    'product_variants' => $product->variants->map(fn($v) => [
                        'id' => $v->id,
                        'sku' => $v->sku ?? '',
                        'price' => $v->price ?? 0,
                        'stock' => $v->stock ?? 0,
                        'image_url' => $v->image_url ?? 'https://via.placeholder.com/150?text=Variant',
                        'status' => $v->status ?? null,
                    ]),
                    'shop' => [
                        'id' => $product->shop->id,
                        'shop_name' => $product->shop->shop_name ?? '',
                    ],
                    'reviews' => $product->reviews->map(fn($r) => [
                        'id' => $r->id,
                        'comment' => $r->comment ?? '',
                        'rating' => $r->rating ?? 0,
                        'images' => is_array($r->images) ? $r->images : (json_decode($r->images, true) ?? []),
                        'created_at' => $r->created_at,
                        'username' => $r->user->username ?? 'Ẩn danh',
                    ]),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi ProductController: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Lỗi máy chủ nội bộ'], 500);
        }
    }
}