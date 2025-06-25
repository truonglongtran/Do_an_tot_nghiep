<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerPlacement;
use App\Models\BannerDisplayLocation;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Banners
            $banners = Banner::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereHas('placements', function ($query) {
                    $query->where('is_active', true)
                          ->whereHas('location', function ($q) {
                              $q->where('location_type', 'platform');
                          });
                })
                ->select('id', 'title', 'img_url', 'link_url')
                ->with(['placements' => function ($query) {
                    $query->orderBy('display_order', 'asc')
                          ->with(['location' => function ($q) {
                              $q->select('id', 'code');
                          }]);
                }])
                ->get();

            // Categories
            $categories = Category::select('id', 'name', 'slug')
                ->whereNull('deleted_at')
                ->take(12)
                ->get();

            // Flash Sales
            $flashSales = FlashSale::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereNull('deleted_at')
                ->with([
                    'productVariant' => function ($query) {
                        $query->where('status', 'active')
                              ->select('id', 'product_id', 'price', 'image_url');
                    },
                    'productVariant.product' => function ($query) {
                        $query->where('status', 'approved')
                              ->select('id', 'shop_id', 'name', 'price');
                    },
                    'productVariant.product.shop' => function ($query) {
                        $query->where('status', 'active')
                              ->select('id', 'shop_name');
                    }
                ])
                ->select('id', 'product_variant_id', 'discount_price', 'stock_limit')
                ->take(8)
                ->get()
                ->filter(function ($fs) {
                    return !is_null($fs->productVariant) &&
                           !is_null($fs->productVariant->product) &&
                           !is_null($fs->productVariant->product->shop);
                });

            // Recommended Products
            $recommendedProducts = Product::where('status', 'approved')
                ->whereNull('deleted_at')
                ->with([
                    'variants' => function ($query) {
                        $query->where('status', 'active')
                              ->select('id', 'product_id', 'price', 'image_url');
                    },
                    'shop' => function ($query) {
                        $query->where('status', 'active')
                              ->select('id', 'shop_name');
                    }
                ])
                ->select('id', 'shop_id', 'name', 'sold_count', 'price')
                ->orderBy('sold_count', 'desc')
                ->get()
                ->filter(function ($p) {
                    return !is_null($p->shop);
                });

            return response()->json([
                'banners' => $banners->map(fn($b) => [
                    'id' => $b->id,
                    'title' => $b->title ?? '',
                    'img_url' => $b->img_url ?? 'https://via.placeholder.com/1200x400?text=Banner',
                    'link_url' => $b->link_url ?? '#',
                    'location_code' => $b->placements->first()->location->code ?? '',
                ]),
                'categories' => $categories->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name ?? '',
                    'slug' => $c->slug ?? '',
                ]),
                'flashSales' => $flashSales->map(fn($fs) => [
                    'id' => $fs->id,
                    'discount_price' => $fs->discount_price ?? 0,
                    'stock_limit' => $fs->stock_limit ?? 0,
                    'product_variant' => [
                        'id' => $fs->productVariant->id,
                        'price' => $fs->productVariant->price ?? 0,
                        'image_url' => $fs->productVariant->image_url ?? 'https://via.placeholder.com/150?text=Product',
                    ],
                    'product' => [
                        'id' => $fs->productVariant->product->id,
                        'name' => $fs->productVariant->product->name ?? '',
                        'price' => $fs->productVariant->product->price ?? 0,
                        'lowest_price' => $fs->productVariant->product->variants->count() > 0 
                            ? $fs->productVariant->product->variants->min('price') 
                            : $fs->productVariant->product->price,
                        'shop' => [
                            'id' => $fs->productVariant->product->shop->id,
                            'shop_name' => $fs->productVariant->product->shop->shop_name ?? '',
                        ],
                    ],
                ]),
                'recommendedProducts' => $recommendedProducts->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name ?? '',
                    'sold_count' => $p->sold_count ?? 0,
                    'price' => $p->price ?? 0,
                    'lowest_price' => $p->variants->count() > 0 ? $p->variants->min('price') : $p->price,
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
        } catch (\Exception $e) {
            \Log::error('Lỗi HomeController: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi máy chủ nội bộ'], 500);
        }
    }
}