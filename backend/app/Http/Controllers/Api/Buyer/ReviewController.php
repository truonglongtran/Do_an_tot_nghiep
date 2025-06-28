<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Store a new review for a product.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Log request data
            \Log::info('Received review request', [
                'request' => $request->except('images'),
                'has_files' => $request->hasFile('images'),
                'buyer_id' => Auth::id() ?? 'unknown',
            ]);

            // Validate input data
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|exists:products,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                \Log::warning('Validation failed in ReviewController::store', [
                    'errors' => $validator->errors()->all(),
                    'request' => $request->except('images'),
                ]);
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                \Log::error('No authenticated user found in ReviewController::store');
                return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập lại'], 401);
            }

            // Check order
            $order = Order::where('id', $request->order_id)
                ->where('buyer_id', $user->id)
                ->where('shipping_status', 'delivered')
                ->first();

            if (!$order) {
                \Log::warning('Invalid order in ReviewController::store', [
                    'order_id' => $request->order_id,
                    'buyer_id' => $user->id,
                ]);
                return response()->json(['success' => false, 'message' => 'Đơn hàng không hợp lệ hoặc chưa được giao'], 403);
            }

            // Check order item and review status
            $orderItem = OrderItem::where('order_id', $request->order_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$orderItem) {
                \Log::warning('Order item not found in ReviewController::store', [
                    'order_id' => $request->order_id,
                    'product_id' => $request->product_id,
                ]);
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong đơn hàng'], 403);
            }

            if ($orderItem->is_reviewed) {
                \Log::warning('Order item already reviewed in ReviewController::store', [
                    'order_id' => $request->order_id,
                    'product_id' => $request->product_id,
                    'order_item_id' => $orderItem->id,
                ]);
                return response()->json(['success' => false, 'message' => 'Sản phẩm này đã được đánh giá'], 403);
            }

            // Check for duplicate review
            $existingReview = Review::where('buyer_id', $user->id)
                ->where('order_id', $request->order_id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingReview) {
                \Log::warning('Duplicate review attempt in ReviewController::store', [
                    'buyer_id' => $user->id,
                    'order_id' => $request->order_id,
                    'product_id' => $request->product_id,
                ]);
                return response()->json(['success' => false, 'message' => 'Bạn đã đánh giá sản phẩm này'], 403);
            }

            DB::beginTransaction();

            // Create review
            $review = new Review();
            $review->buyer_id = $user->id;
            $review->order_id = $request->order_id;
            $review->product_id = $request->product_id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;

            // Save review to get ID
            $review->save();

            // Handle image uploads
            $images = [];
            if ($request->hasFile('images')) {
                $index = 1;
                foreach ($request->file('images') as $image) {
                    if (!$image->isValid()) {
                        \Log::error('Invalid image file in ReviewController::store', [
                            'file' => $image->getClientOriginalName(),
                            'size' => $image->getSize(),
                            'mime' => $image->getMimeType(),
                        ]);
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'File hình ảnh không hợp lệ: ' . $image->getClientOriginalName()], 422);
                    }

                    // Define path: storage/app/public/reviews/{review_id}/img{index}.jpg
                    $extension = $image->getClientOriginalExtension();
                    $filename = "img{$index}.{$extension}";
                    $path = $image->storeAs("reviews/{$review->id}", $filename, 'public');

                    if (!$path) {
                        \Log::error('Failed to store image in ReviewController::store', [
                            'file' => $image->getClientOriginalName(),
                            'review_id' => $review->id,
                            'filename' => $filename,
                        ]);
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Lỗi khi lưu trữ hình ảnh'], 500);
                    }

                    $images[] = Storage::url($path);
                    $index++;
                }
            }

            // Update review with images
            $review->images = json_encode($images);
            $review->save();

            // Update order item review status
            $orderItem->is_reviewed = true;
            $orderItem->save();

            DB::commit();

            \Log::info('Review created successfully', [
                'review_id' => $review->id,
                'buyer_id' => $user->id,
                'order_id' => $request->order_id,
                'product_id' => $review->product_id,
                'order_item_id' => $orderItem->id,
                'images' => $images,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gửi đánh giá thành công',
                'review' => [
                    'id' => $review->id,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'product_id' => $review->product_id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'images' => $review->images ? json_decode($review->images, true) : [],
                    'created_at' => $review->created_at->toDateTimeString(),
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in ReviewController::store: ' . $e->getMessage(), [
                'buyer_id' => Auth::id() ?? 'unknown',
                'request' => $request->except('images'),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Lỗi khi gửi đánh giá: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of reviews for the authenticated buyer.
     */
    public function index()
    {
        try {
            $reviews = Review::where('buyer_id', Auth::id())
                ->with([
                    'user:id,name,email',
                    'product:id,name,image_url',
                ])
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user' => $review->user ? [
                            'id' => $review->user->id,
                            'name' => $review->user->name,
                            'email' => $review->user->email,
                        ] : null,
                        'product' => $review->product ? [
                            'id' => $review->product->id,
                            'name' => $review->product->name,
                            'image_url' => $review->product->image_url,
                        ] : null,
                        'order_id' => $review->order_id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'images' => $review->images ? json_decode($review->images, true) : [],
                        'created_at' => $review->created_at->toDateTimeString(),
                    ];
                });

            return response()->json(['success' => true, 'reviews' => $reviews], 200);
        } catch (\Exception $e) {
            \Log::error('Error in ReviewController::index: ' . $e->getMessage(), [
                'buyer_id' => Auth::id() ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Lỗi tải đánh giá'], 500);
        }
    }

    /**
     * Check if an order is fully reviewed.
     *
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOrderReviewStatus($orderId)
    {
        try {
            $order = Order::where('id', $orderId)
                ->where('buyer_id', Auth::id())
                ->where('shipping_status', 'delivered')
                ->first();

            if (!$order) {
                \Log::warning('Invalid order in ReviewController::checkOrderReviewStatus', [
                    'order_id' => $orderId,
                    'buyer_id' => Auth::id() ?? 'unknown',
                ]);
                return response()->json(['success' => false, 'message' => 'Đơn hàng không hợp lệ hoặc chưa được giao'], 403);
            }

            $unreviewedItems = OrderItem::where('order_id', $orderId)
                ->where('is_reviewed', false)
                ->count();

            $canReview = $unreviewedItems > 0;

            \Log::info('Checked order review status', [
                'order_id' => $orderId,
                'can_review' => $canReview,
                'unreviewed_items' => $unreviewedItems,
            ]);

            return response()->json([
                'success' => true,
                'can_review' => $canReview,
                'unreviewed_items' => $unreviewedItems,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in ReviewController::checkOrderReviewStatus: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'buyer_id' => Auth::id() ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Lỗi khi kiểm tra trạng thái đánh giá'], 500);
        }
    }
}