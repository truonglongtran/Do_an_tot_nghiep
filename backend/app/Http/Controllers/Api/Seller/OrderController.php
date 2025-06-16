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
                ->with([
                    'buyer:id,email', // Load email và name của người mua
                    'items.product:id,name', // Load tên sản phẩm
                    'items.productVariant:id,price', // Load giá của biến thể
                    'items.productVariant.variantAttributes.attribute:id,name', // Load tên thuộc tính
                    'items.productVariant.variantAttributes.attributeValue:id,value' // Load giá trị thuộc tính
                ]);

            Log::info('Fetching orders với relations', ['seller_id' => $sellerId]);
            $orders = $query->get();

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching seller orders:', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
                ->with([
                    'buyer',
                    'items.product',
                    'items.productVariant.variantAttributes.attribute',
                    'items.productVariant.variantAttributes.attributeValue',
                ])
                ->findOrFail($id);

            Log::info('Seller order fetched', [
                'seller_id' => $sellerId,
                'order_id' => $id,
                'item_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    $attributes = $item->productVariant && $item->productVariant->variantAttributes
                        ? $item->productVariant->variantAttributes->map(function ($attr) {
                            return [
                                'attribute_name' => $attr->attribute ? $attr->attribute->name : 'N/A',
                                'attribute_value' => $attr->attributeValue ? $attr->attributeValue->value : 'N/A',
                            ];
                        })->toArray()
                        : [];
                    return [
                        'id' => $item->id,
                        'variant_id' => $item->product_variant_id,
                        'product_name' => $item->product ? $item->product->name : 'N/A',
                        'variant_attributes' => $attributes,
                        'variant_price' => $item->productVariant ? $item->productVariant->price : ($item->price ?? 0),
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
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết đơn hàng: ' . $e->getMessage(),
            ], 404);
        }
    }
}