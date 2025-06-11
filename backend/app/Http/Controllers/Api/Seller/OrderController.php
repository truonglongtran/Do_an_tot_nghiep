<?php
namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $orders = Order::where('seller_id', $sellerId)
                ->with(['buyer', 'items.product', 'items.productVariant'])
                ->get();

            Log::info('Seller orders fetched', [
                'seller_id' => $sellerId,
                'order_count' => $orders->count(),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'item_count' => $order->items->count(),
                        'items' => $order->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'variant_id' => $item->product_variant_id,
                                'price' => $item->productVariant ? $item->productVariant->price : null,
                                'quantity' => $item->quantity,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching seller orders', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đơn hàng: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $sellerId = $request->user()->id;
            $order = Order::where('seller_id', $sellerId)
                ->with(['buyer', 'items.product', 'items.productVariant'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching seller order', [
                'seller_id' => $request->user()->id,
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết đơn hàng: ' . $e->getMessage(),
            ], 404);
        }
    }

    public function updateShippingStatus(Request $request, $id)
    {
        Log::info('Update Shipping Status Request by Seller', [
            'order_id' => $id,
            'seller_id' => $request->user()->id,
            'payload' => $request->all(),
        ]);

        try {
            $validated = $request->validate([
                'shipping_status' => 'required|in:pending,processing,shipping,delivered,failed,return',
            ]);

            $sellerId = $request->user()->id;
            $order = Order::where('seller_id', $sellerId)->findOrFail($id);
            $order->shipping_status = $validated['shipping_status'];
            $order->save();

            Log::info('Shipping Status Updated by Seller', [
                'order_id' => $id,
                'seller_id' => $sellerId,
                'shipping_status' => $validated['shipping_status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái vận chuyển thành công',
                'data' => $order->load(['buyer', 'items.product', 'items.productVariant']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error in Seller Shipping Status', [
                'seller_id' => $request->user()->id,
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update Shipping Status Error by Seller', [
                'order_id' => $id,
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi cập nhật trạng thái vận chuyển',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}