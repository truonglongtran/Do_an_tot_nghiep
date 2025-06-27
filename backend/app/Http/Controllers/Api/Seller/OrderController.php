<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
    public function revenue(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $filter = $request->input('filter', 'daily');

            $query = Order::where('seller_id', $sellerId)
                ->where('settled_status', 'settled');

            if ($filter === 'daily') {
                $query->whereDate('settled_at', Carbon::today());
            } elseif ($filter === 'weekly') {
                $query->whereBetween('settled_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($filter === 'monthly') {
                $query->whereMonth('settled_at', Carbon::now()->month)
                      ->whereYear('settled_at', Carbon::now()->year);
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

            $query = Order::where('seller_id', $sellerId)
                ->where('settled_status', 'settled');

            if ($filter === 'daily') {
                $query->whereDate('settled_at', Carbon::today());
            } elseif ($filter === 'weekly') {
                $query->whereBetween('settled_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($filter === 'monthly') {
                $query->whereMonth('settled_at', Carbon::now()->month)
                      ->whereYear('settled_at', Carbon::now()->year);
            }

            $orders = $query->with(['items.product', 'items.productVariant'])->get();

            // Tạo thư mục reports nếu chưa tồn tại
            $directory = storage_path('app/public/reports');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = 'reports/revenue_' . $filter . '_' . Carbon::now()->timestamp . '.csv';
            $filePath = storage_path('app/public/' . $filename);
            $file = fopen($filePath, 'w');
            if ($file === false) {
                throw new \Exception('Không thể mở file để ghi: ' . $filePath);
            }

            fputcsv($file, ['Order ID', 'Date', 'Total', 'Items']);

            foreach ($orders as $order) {
                $items = $order->items->map(function ($item) {
                    return $item->product->name . ' (' . $item->quantity . ')';
                })->implode(', ');
                fputcsv($file, [
                    $order->id,
                    $order->settled_at->toDateTimeString(),
                    $order->total,
                    $items,
                ]);
            }
            fclose($file);

            $report = Report::create([
                'report_type' => $filter,
                'file_url' => Storage::url($filename),
                'created_at' => Carbon::now(),
            ]);

            Log::info('Report exported', [
                'seller_id' => $sellerId,
                'report_id' => $report->id,
                'file_url' => $report->file_url,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'file_url' => $report->file_url,
                ],
            ]);
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