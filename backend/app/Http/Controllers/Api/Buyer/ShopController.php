<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopFollower;
use App\Models\Banner;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function show(Request $request, $id)
    {
        try {
            $shop = Shop::where('id', $id)
                ->where('status', 'active')
                ->select('id', 'shop_name', 'avatar_url', 'cover_image_url', 'pickup_address')
                ->with([
                    'products' => function ($q) {
                        $q->where('status', 'approved')
                          ->select('id', 'shop_id', 'name', 'sold_count', 'price')
                          ->with([
                              'variants' => function ($v) {
                                  $v->where('status', 'active')
                                    ->select('id', 'product_id', 'price', 'image_url', 'stock');
                              },
                              'shop' => function ($s) {
                                  $s->select('id', 'shop_name');
                              }
                          ])
                          ->take(20);
                    }
                ])
                ->firstOrFail();

            $user = $request->user();
            $isFollowing = $user ? ShopFollower::where('user_id', $user->id)
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->exists() : false;

            // Lấy banner cho shop
            $banners = Banner::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereHas('placements', function ($query) use ($id) {
                    $query->where('is_active', true)
                          ->where('shop_id', $id)
                          ->whereHas('location', function ($q) {
                              $q->where('location_type', 'shop');
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

            return response()->json([
                'shop' => [
                    'id' => $shop->id,
                    'shop_name' => $shop->shop_name,
                    'avatar_url' => $shop->avatar_url,
                    'cover_image_url' => $shop->cover_image_url,
                    'pickup_address' => $shop->pickup_address,
                    'is_following' => $isFollowing,
                    'products' => $shop->products->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'sold_count' => $p->sold_count,
                        'price' => $p->price,
                        'shop' => $p->shop ? [
                            'id' => $p->shop->id,
                            'shop_name' => $p->shop->shop_name,
                        ] : null,
                        'variants' => $p->variants->map(fn($v) => [
                            'id' => $v->id,
                            'price' => $v->price,
                            'image_url' => $v->image_url,
                            'stock' => $v->stock,
                        ]),
                        'lowest_price' => $p->variants->count() > 0 ? $p->variants->min('price') : $p->price,
                        'product_variant' => $p->variants->sortBy('price')->first() ? [
                            'id' => $p->variants->sortBy('price')->first()->id,
                            'price' => $p->variants->sortBy('price')->first()->price,
                            'image_url' => $p->variants->sortBy('price')->first()->image_url,
                            'stock' => $p->variants->sortBy('price')->first()->stock,
                        ] : null,
                    ]),
                ],
                'banners' => $banners->map(fn($b) => [
                    'id' => $b->id,
                    'title' => $b->title ?? '',
                    'img_url' => $b->img_url ?? 'https://via.placeholder.com/1200x400?text=Banner',
                    'link_url' => $b->link_url ?? '#',
                    'location_code' => $b->placements->first()->location->code ?? '',
                ]),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Shop not found: ID=' . $id);
            return response()->json(['error' => 'Cửa hàng không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi ShopController@show: ' . $e->getMessage() . ' | Shop ID: ' . $id);
            return response()->json(['error' => 'Lỗi tải thông tin cửa hàng: ' . $e->getMessage()], 500);
        }
    }

    public function follow(Request $request, $id)
    {
        try {
            $user = $request->user();
            $shop = Shop::where('id', $id)
                ->where('status', 'active')
                ->firstOrFail();

            $existing = ShopFollower::where('user_id', $user->id)
                ->where('shop_id', $shop->id)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Đã theo dõi cửa hàng này'], 200);
            }

            ShopFollower::create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ]);

            return response()->json(['message' => 'Theo dõi cửa hàng thành công'], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Shop not found for follow: ID=' . $id);
            return response()->json(['error' => 'Cửa hàng không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi ShopController@follow: ' . $e->getMessage() . ' | Shop ID: ' . $id);
            return response()->json(['error' => 'Lỗi khi theo dõi cửa hàng'], 500);
        }
    }

    public function unfollow(Request $request, $id)
    {
        try {
            $user = $request->user();
            $follow = ShopFollower::where('user_id', $user->id)
                ->where('shop_id', $id)
                ->whereNull('deleted_at')
                ->firstOrFail();

            $follow->delete();

            return response()->json(['message' => 'Bỏ theo dõi cửa hàng thành công'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Follow record not found for unfollow: Shop ID=' . $id);
            return response()->json(['error' => 'Không tìm thấy bản ghi theo dõi'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi ShopController@unfollow: ' . $e->getMessage() . ' | Shop ID: ' . $id);
            return response()->json(['error' => 'Lỗi khi bỏ theo dõi cửa hàng'], 500);
        }
    }
}