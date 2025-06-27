<?php
namespace App\Http\Controllers\Buyer;

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
    public function index()
    {
        try {
            $reviews = Review::where('user_id', Auth::id())
                ->with(['product', 'productVariant'])
                ->get();
            return response()->json(['reviews' => $reviews], 200);
        } catch (\Exception $e) {
            \Log::error('Error in ReviewController::index: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi tải đánh giá'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|exists:products,id',
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 422);
            }

            $user = Auth::user();
            $order = Order::where('id', $request->order_id)
                ->where('buyer_id', $user->id)
                ->where('shipping_status', 'delivered')
                ->firstOrFail();

            $orderItem = OrderItem::where('order_id', $request->order_id)
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->product_variant_id ?: null)
                ->firstOrFail();

            // Kiểm tra xem đã đánh giá chưa
            $existingReview = Review::where('user_id', $user->id)
                ->where('order_id', $request->order_id)
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->product_variant_id ?: null)
                ->exists();

            if ($existingReview) {
                return response()->json(['message' => 'Bạn đã đánh giá sản phẩm này'], 403);
            }

            DB::beginTransaction();

            $review = new Review();
            $review->user_id = $user->id;
            $review->order_id = $request->order_id;
            $review->product_id = $request->product_id;
            $review->product_variant_id = $request->product_variant_id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;

            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reviews', 'public');
                    $images[] = '/storage/images/reviews/' . $path;
                }
                $review->images = $images;
            }

            $review->save();
            DB::commit();

            return response()->json(['message' => 'Gửi đánh giá thành công', 'review' => $review], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in ReviewController::store: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi gửi đánh giá'], 500);
        }
    }
}