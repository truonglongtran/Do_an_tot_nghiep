<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Report;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $query = Order::where('seller_id', $sellerId)
                ->with([
                    'buyer:id,email',
                    'items.product:id,name',
                    'items.productVariant:id,price',
                    'items.productVariant.variantAttributes.attribute:id,name',
                    'items.productVariant.variantAttributes.attributeValue:id,value'
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

    public function revenue(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $filter = $request->input('filter', 'daily');

            $query = Order::where('seller_id', $sellerId)
                ->where('settled_status', 'settled');

            if ($filter === 'daily') {
                $query->whereDate('settled_at', Carbon::today());
            } elseif ($filter === 'monthly') {
                $query->whereMonth('settled_at', Carbon::now()->month)
                      ->whereYear('settled_at', Carbon::now()->year);
            } else {
                throw new \Exception('Loại bộ lọc không hợp lệ');
            }

            $totalRevenue = $query->sum('total');
            $totalOrders = $query->count();

            $topProduct = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.seller_id', $sellerId)
                ->where('orders.settled_status', 'settled')
                ->groupBy('products.id', 'products.name')
                ->select('products.id', 'products.name', \DB::raw('SUM(order_items.quantity) as total_sold'))
                ->orderByDesc('total_sold')
                ->first();

            $orders = $query->with([
                'buyer:id,email',
                'items.product:id,name',
                'items.productVariant:id,price',
                'items.productVariant.variantAttributes.attribute:id,name',
                'items.productVariant.variantAttributes.attributeValue:id,value'
            ])->get();

            Log::info('Revenue fetched', [
                'seller_id' => $sellerId,
                'filter' => $filter,
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'top_product' => $topProduct ? [
                        'id' => $topProduct->id,
                        'name' => $topProduct->name,
                        'total_sold' => (int)$topProduct->total_sold
                    ] : null,
                    'orders' => $orders,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching revenue', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu doanh thu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportReport(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $filter = $request->input('filter', 'daily');

            // Fetch shop name, fallback to user name if shop not found
            $shop = Shop::where('owner_id', $sellerId)->first();
            $shopName = $shop ? $shop->name : $request->user()->name;

            // Build query for orders
            $query = Order::where('seller_id', $sellerId)
                ->where('settled_status', 'settled');

            if ($filter === 'daily') {
                $query->whereDate('settled_at', Carbon::today());
            } elseif ($filter === 'monthly') {
                $query->whereMonth('settled_at', Carbon::now()->month)
                      ->whereYear('settled_at', Carbon::now()->year);
            } else {
                throw new \Exception('Loại bộ lọc không hợp lệ');
            }

            // Calculate total revenue and orders
            $totalRevenue = $query->sum('total');
            $totalOrders = $query->count();
            $orders = $query->with(['items.product', 'items.productVariant'])->get();

            // Generate CSV content
            $csvData = [];
            $csvData[] = ['Shop Name', $shopName];
            $csvData[] = ['Report Type', $filter];
            $csvData[] = ['Total Revenue', number_format($totalRevenue, 2, '.', '')];
            $csvData[] = ['Total Orders', $totalOrders];
            $csvData[] = []; // Empty row for separation
            $csvData[] = ['Order ID', 'Date', 'Total', 'Items'];

            foreach ($orders as $order) {
                $items = $order->items->map(function ($item) {
                    return $item->product->name . ' (' . $item->quantity . ')';
                })->implode(', ');
                $csvData[] = [
                    $order->id,
                    $order->settled_at->toDateTimeString(),
                    number_format($order->total, 2, '.', ''),
                    $items,
                ];
            }

            // Create CSV content as a string
            $csvContent = '';
            foreach ($csvData as $row) {
                $csvContent .= implode(',', array_map(function ($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row)) . "\n";
            }

            // Log the export action
            Log::info('Report exported for seller', [
                'seller_id' => $sellerId,
                'filter' => $filter,
                'shop_name' => $shopName,
            ]);

            // Return CSV as a download response
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="revenue_' . $filter . '_' . Carbon::now()->timestamp . '.csv"',
            ];

            return response($csvContent, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error exporting report', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất báo cáo: ' . $e->getMessage(),
            ], 500);
        }
    }
}