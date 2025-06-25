<?php
// app/Http/Controllers/Api/Buyer/ReviewController.php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $reviews = Review::where('buyer_id', $user->id)
            ->with([
                'product' => function ($q) {
                    $q->select('id', 'name', 'images');
                }
            ])
            ->select('id', 'product_id', 'rating', 'comment', 'images', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json(['reviews' => $reviews]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'string|url',
        ]);

        $user = $request->user();
        $order = Order::where('id', $request->order_id)
            ->where('buyer_id', $user->id)
            ->where('order_status', 'completed')
            ->firstOrFail();

        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $request->product_id)
            ->firstOrFail();

        $existingReview = Review::where('order_id', $request->order_id)
            ->where('product_id', $request->product_id)
            ->where('buyer_id', $user->id)
            ->exists();

        if ($existingReview) {
            return response()->json(['error' => 'You have already reviewed this product'], 400);
        }

        $review = Review::create([
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'buyer_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'images' => $request->images,
        ]);

        return response()->json(['message' => 'Review submitted', 'review' => $review], 201);
    }
}