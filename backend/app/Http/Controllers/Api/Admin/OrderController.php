<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['buyer', 'seller', 'items.product', 'items.productVariant'])->get();
        Log::info('Orders fetched', [
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
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with(['buyer', 'seller', 'items.product', 'items.productVariant'])->findOrFail($id);
        return response()->json($order);
    }

    public function updateSettledStatus(Request $request, $id)
    {
        Log::info('Update Settled Status Request', ['order_id' => $id, 'payload' => $request->all()]);
        try {
            $validated = $request->validate([
                'settled_status' => 'required|in:unsettled,settled',
            ]);

            $order = Order::findOrFail($id);
            $order->settled_status = $validated['settled_status'];
            $order->settled_at = $validated['settled_status'] === 'settled' ? now() : null;
            $order->save();

            Log::info('Settled Status Updated', ['order_id' => $id, 'settled_status' => $validated['settled_status']]);

            return response()->json([
                'message' => 'Cập nhật trạng thái thanh toán thành công',
                'order' => $order->load(['buyer', 'seller', 'items.product', 'items.productVariant'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error in Settled Status', ['errors' => $e->errors(), 'payload' => $request->all()]);
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update Settled Status Error', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Lỗi server khi cập nhật trạng thái thanh toán',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateShippingStatus(Request $request, $id)
    {
        Log::info('Update Shipping Status Request', ['order_id' => $id, 'payload' => $request->all()]);
        try {
            $validated = $request->validate([
                'shipping_status' => 'required|in:pending,processing,shipping,delivered,failed,return',
            ]);

            $order = Order::findOrFail($id);
            $order->shipping_status = $validated['shipping_status'];
            $order->save();

            Log::info('Shipping Status Updated', ['order_id' => $id, 'shipping_status' => $validated['shipping_status']]);

            return response()->json([
                'message' => 'Cập nhật trạng thái vận chuyển thành công',
                'order' => $order->load(['buyer', 'seller', 'items.product', 'items.productVariant'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error in Shipping Status', ['errors' => $e->errors(), 'payload' => $request->all()]);
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update Shipping Status Error', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Lỗi server khi cập nhật trạng thái vận chuyển',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateOrderStatus(Request $request, $id)
    {
        Log::info('Update Order Status Request', ['order_id' => $id, 'payload' => $request->all()]);
        try {
            $validated = $request->validate([
                'order_status' => 'required|in:pending,paid,canceled',
            ]);

            $order = Order::findOrFail($id);
            $order->order_status = $validated['order_status'];
            $order->save();

            Log::info('Order Status Updated', ['order_id' => $id, 'order_status' => $validated['order_status']]);

            return response()->json([
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'order' => $order->load(['buyer', 'seller', 'items.product', 'items.productVariant'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error in Order Status', ['errors' => $e->errors(), 'payload' => $request->all()]);
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update Order Status Error', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Lỗi server khi cập nhật trạng thái đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Delete Order Request', ['order_id' => $id]);
        try {
            $order = Order::findOrFail($id);
            $order->delete();
            Log::info('Order Deleted', ['order_id' => $id]);
            return response()->json(['message' => 'Xóa đơn hàng thành công']);
        } catch (\Exception $e) {
            Log::error('Delete Order Error', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Xóa đơn hàng thất bại', 'error' => $e->getMessage()], 500);
        }
    }
}