<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function getBanners(Request $request)
    {
        try {
            $locationType = $request->query('location_type'); // e.g., 'platform', 'shop', 'campaign', 'product_page'
            $locationName = $request->query('location_name'); // e.g., 'home_page', 'shop_page'
            $shopId = $request->query('shop_id'); // Optional, for shop-specific banners
            $productId = $request->query('product_id'); // Optional, for product-specific banners

            $query = Banner::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereHas('placements', function ($query) use ($locationType, $locationName, $shopId, $productId) {
                    $query->where('is_active', true)
                          ->whereHas('location', function ($q) use ($locationType, $locationName) {
                              if ($locationType) {
                                  $q->where('location_type', $locationType);
                              }
                              if ($locationName) {
                                  $q->where('location_name', $locationName);
                              }
                          });

                    if ($shopId) {
                        $query->where('shop_id', $shopId);
                    }
                    // Add product_id filter if needed for product_page
                })
                ->select('id', 'title', 'img_url', 'link_url')
                ->with(['placements' => function ($query) use ($shopId) {
                    $query->where('is_active', true)
                          ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                          ->orderBy('display_order', 'asc')
                          ->select('id', 'banner_id', 'display_order');
                }]);

            $banners = $query->get();

            return response()->json([
                'banners' => $banners->map(fn($b) => [
                    'id' => $b->id,
                    'title' => $b->title ?? '',
                    'img_url' => $b->img_url ?? 'https://via.placeholder.com/1200x400?text=Banner',
                    'link_url' => $b->link_url ?? '#',
                    'display_order' => $b->placements->first()->display_order ?? 0,
                ])->sortBy('display_order')->values(),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('BannerController Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching banners'], 500);
        }
    }
}