<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for the authenticated seller's shop.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showReviews(Request $request)
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

            $reviews = Review::whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
                ->with([
                    'user:id,email',
                    'product:id,name,shop_id',
                    'product.shop:id,shop_name'
                ])
                ->get();

            // Đảm bảo is_hidden được bao gồm
            $reviews->each(function ($review) {
                $review->is_hidden = $review->is_hidden ?? false;
            });

            return response()->json([
                'success' => true,
                'data' => $reviews,
                'shop' => [
                    'id' => $shop->id,
                    'name' => $shop->shop_name,
                    'average_rating' => round($reviews->whereNotNull('rating')->avg('rating'), 2) ?? 0,
                    'good_reviews' => $reviews->where('rating', '>=', 3)->count(),
                    'bad_reviews' => $reviews->where('rating', '<', 3)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reviews for seller shop', [
                'seller_id' => $request->user()->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải đánh giá: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hide a review for the authenticated seller's shop.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /*
    public function hide($id, Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán'
                ], 404);
            }

            $review = Review::findOrFail($id);

            if ($review->product->shop_id !== $shop->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền ẩn đánh giá này'
                ], 403);
            }

            $review->update(['is_hidden' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Ẩn đánh giá thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error hiding review for seller', [
                'seller_id' => $request->user()->id ?? 'unknown',
                'review_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể ẩn đánh giá: ' . $e->getMessage()
            ], 500);
        }
    }
    */
}