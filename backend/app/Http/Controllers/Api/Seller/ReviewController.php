<?php
namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function showReviews(Request $request)
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

            $reviews = Review::whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
                ->with([
                    'buyer:id,email',
                    'product:id,name,shop_id',
                    'product.shop:id,name as shop_name'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reviews,
                'shop' => [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'average_rating' => round($reviews->whereNotNull('rating')->avg('rating'), 2) ?? 0,
                    'good_reviews' => $reviews->where('rating', '>=', 3)->count(),
                    'bad_reviews' => $reviews->where('rating', '<', 3)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reviews for seller shop', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải đánh giá'
            ], 500);
        }
    }
}