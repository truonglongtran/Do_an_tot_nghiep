<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Report;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Report::query();

            // Filter by report_type
            if ($request->has('report_type') && $request->report_type !== 'all') {
                $query->where('report_type', $request->report_type);
            }

            // Filter by shop_name (optional, since now we use 'All Shops')
            if ($request->has('shop_name') && $request->shop_name) {
                $query->where('shop_name', 'like', '%' . $request->shop_name . '%');
            }

            // Filter by date range
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $reports = $query->get()->map(function ($report) {
                return [
                    'id' => $report->id,
                    'report_type' => $report->report_type,
                    'shop_name' => $report->shop_name,
                    'file_url' => $report->file_url,
                    'created_at' => $report->created_at->toIso8601String(),
                ];
            });

            $reportTypes = Report::distinct('report_type')->pluck('report_type')->toArray();
            $shopNames = Report::distinct('shop_name')->pluck('shop_name')->toArray();

            return response()->json([
                'reports' => $reports,
                'report_types' => $reportTypes,
                'shop_names' => $shopNames,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to fetch reports'], 500);
        }
    }

    public function generateReports()
    {
        try {
            $shops = Shop::all();
            $today = Carbon::today();
            $year = $today->year;
            $month = $today->format('m');

            foreach ($shops as $shop) {
                $sellerId = $shop->owner_id;
                $shopName = $shop->name ?? User::find($sellerId)->name ?? 'Unknown Shop';
                // Sanitize shop_name for file name
                $safeShopName = preg_replace('/[^A-Za-z0-9_-]/', '_', $shopName);

                // Generate reports for daily, monthly, and yearly
                $this->generateReportForShop(
                    $sellerId,
                    $shopName,
                    'daily',
                    "public/reports/admin/$year/$month/{$today->format('Y-m-d')}_{$safeShopName}.csv"
                );
                $this->generateReportForShop(
                    $sellerId,
                    $shopName,
                    'monthly',
                    "public/reports/admin/$year/{$year}-{$month}_{$safeShopName}.csv"
                );
                $this->generateReportForShop(
                    $sellerId,
                    $shopName,
                    'yearly',
                    "public/reports/admin/{$year}_{$safeShopName}.csv"
                );
            }

            Log::info('All reports generated successfully', [
                'shops_count' => $shops->count(),
                'date' => $today->toDateString(),
            ]);

            return response()->json(['success' => true, 'message' => 'Báo cáo đã được tạo']);
        } catch (\Exception $e) {
            Log::error('Error generating reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Lỗi khi tạo báo cáo'], 500);
        }
    }

    private function generateReportForShop($sellerId, $shopName, $reportType, $filePath)
    {
        try {
            // Build query for orders, identical to SellerOrderController::exportReport
            $query = Order::where('seller_id', $sellerId)
                ->where('settled_status', 'settled');

            if ($reportType === 'daily') {
                $query->whereDate('settled_at', Carbon::today());
            } elseif ($reportType === 'monthly') {
                $query->whereMonth('settled_at', Carbon::now()->month)
                      ->whereYear('settled_at', Carbon::now()->year);
            } elseif ($reportType === 'yearly') {
                $query->whereYear('settled_at', Carbon::now()->year);
            } else {
                throw new \Exception('Loại báo cáo không hợp lệ');
            }

            // Calculate total revenue and orders
            $totalRevenue = $query->sum('total');
            $totalOrders = $query->count();
            $orders = $query->with(['items.product', 'items.productVariant'])->get();

            // Generate CSV content, identical to SellerOrderController::exportReport
            $csvData = [];
            $csvData[] = ['Shop Name', $shopName];
            $csvData[] = ['Report Type', $reportType];
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

            // Create directory if it doesn't exist
            Storage::makeDirectory(dirname($filePath));

            // Save CSV to storage
            Storage::put($filePath, $csvContent);

            // Save to reports table
            Report::create([
                'report_type' => $reportType,
                'shop_name' => $shopName,
                'file_url' => Storage::url($filePath),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            Log::info('Report generated', [
                'shop_name' => $shopName,
                'report_type' => $reportType,
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating report for shop', [
                'seller_id' => $sellerId,
                'report_type' => $reportType,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Rethrow to be caught in generateReports
        }
    }
}