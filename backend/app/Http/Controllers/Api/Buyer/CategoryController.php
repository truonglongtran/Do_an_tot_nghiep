<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::select('id', 'name', 'slug')
            ->whereNull('deleted_at')
            ->get();

        return response()->json(['categories' => $categories], 200);
    }

    public function show($slug)
    {
        try {
            // Tìm danh mục theo slug
            $category = Category::where('slug', $slug)
                ->whereNull('deleted_at')
                ->select('id', 'name', 'slug')
                ->firstOrFail();

            // Lấy sản phẩm thuộc danh mục này
            $products = Product::where('status', 'approved')
                ->whereNull('deleted_at')
                ->where('category_id', $category->id)
                ->whereHas('shop', function ($query) {
                    $query->where('status', 'active');
                })
                ->with([
                    'variants' => function ($query) {
                        $query->where('status', 'approved')
                              ->select('id', 'product_id', 'price', 'image_url')
                              ->take(1);
                    },
                    'shop' => function ($query) {
                        $query->where('status', 'active')
                              ->select('id', 'shop_name');
                    }
                ])
                ->select('id', 'shop_id', 'name', 'sold_count')
                ->take(20)
                ->get();

            return response()->json([
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name ?? '',
                    'slug' => $category->slug ?? '',
                ],
                'products' => $products->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name ?? '',
                    'sold_count' => $p->sold_count ?? 0,
                    'product_variant' => $p->variants->first() ? [
                        'id' => $p->variants->first()->id,
                        'price' => $p->variants->first()->price ?? 0,
                        'image_url' => $p->variants->first()->image_url ?? 'https://via.placeholder.com/150?text=Product',
                    ] : null,
                    'shop' => [
                        'id' => $p->shop->id,
                        'shop_name' => $p->shop->shop_name ?? '',
                    ],
                ]),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Danh mục không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi CategoryController: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Lỗi máy chủ nội bộ'], 500);
        }
    }
}