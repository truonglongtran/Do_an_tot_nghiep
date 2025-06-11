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
            $query = Order::where('seller_id', $sellerId)
                ->with(['buyer', 'items.product', 'items.productVariant']);

            // Sử dụng logic OR cho shipping_status và order_status
            if ($request->has('shipping_status') || $request->has('order_status')) {
                $query->where(function ($q) use ($request) {
                    if ($request->has('shipping_status')) {
                        $shippingStatuses = is_array($request->input('shipping_status'))
                            ? $request->input('shipping_status')
                            : explode(',', $request->input('shipping_status'));
                        $q->orWhereIn('shipping_status', $shippingStatuses);
                    }
                    if ($request->has('order_status')) {
                        $orderStatuses = is_array($request->input('order_status'))
                            ? $request->input('order_status')
                            : explode(',', $request->input('order_status'));
                        $q->orWhereIn('order_status', $orderStatuses);
                    }
                });
            }

            // Thêm log để kiểm tra truy vấn SQL
            Log::info('Truy vấn SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            $orders = $query->get();

            Log::info('Seller orders fetched', [
                'seller_id' => $sellerId,
                'order_count' => $orders->count(),
                'filters' => [
                    'shipping_status' => $request->input('shipping_status'),
                    'order_status' => $request->input('order_status'),
                ],
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'item_count' => $order->items->count(),
                        'items' => $order->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'variant_id' => $item->product_variant_id,
                                'product_name' => $item->product ? $item->product->name : null,
                                'variant_color' => $item->productVariant ? $item->productVariant->color : null,
                                'variant_size' => $item->productVariant ? $item->productVariant->size : null,
                                'variant_price' => $item->productVariant ? $item->productVariant->price : null,
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

            Log::info('Seller order fetched', [
                'seller_id' => $sellerId,
                'order_id' => $id,
                'item_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'variant_id' => $item->product_variant_id,
                        'product_name' => $item->product ? $item->product->name : null,
                        'variant_color' => $item->productVariant ? $item->productVariant->color : null,
                        'variant_size' => $item->productVariant ? $item->productVariant->size : null,
                        'variant_price' => $item->productVariant ? $item->productVariant->price : null,
                        'quantity' => $item->quantity,
                    ];
                })->toArray(),
            ]);

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
}