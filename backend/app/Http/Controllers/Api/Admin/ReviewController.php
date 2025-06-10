<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function index()
    {
        try {
            $shops = Shop::with([
                'owner:id,email',
                'shippingPartners:id,name',
            ])
            ->withCount('reviews')
            ->get()
            ->map(function ($shop) {
                $reviewsQuery = Review::whereHas('product', function ($query) use ($shop) {
                    $query->where('shop_id', $shop->id);
                })->whereNotNull('rating');
                $averageRating = $reviewsQuery->avg('rating') ?? 0;
                $goodReviews = $reviewsQuery->where('rating', '>=', 3)->count();
                $badReviews = $reviewsQuery->where('rating', '<', 3)->count();
                return array_merge($shop->toArray(), [
                    'average_rating' => round($averageRating, 2),
                    'good_reviews' => $goodReviews,
                    'bad_reviews' => $badReviews,
                ]);
            });
            return response()->json($shops);
        } catch (\Exception $e) {
            Log::error('Error fetching shops with review counts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to fetch shops'], 500);
        }
    }

    public function showReviews($shopId)
    {
        try {
            $reviews = Review::whereHas('product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->with([
                    'buyer:id,email',
                    'product:id,name,shop_id',
                    'product.shop:id,shop_name'
                ])
                ->get();
            return response()->json($reviews);
        } catch (\Exception $e) {
            Log::error('Error fetching reviews for shop', [
                'shop_id' => $shopId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to fetch reviews'], 500);
        }
    }
}