<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\Report;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReports extends Command
{
    protected $signature = 'reports:generate';
    protected $description = 'Generate daily, monthly, and yearly reports for all shops in a single CSV file';

    public function handle()
    {
        try {
            $today = Carbon::today();
            $year = $today->year;
            $month = $today->format('m');

            // Generate reports for daily, monthly, and yearly
            $this->generateCombinedReport('daily', "public/reports/admin/$year/$month/{$today->format('Y-m-d')}_all_shops.csv");
            $this->generateCombinedReport('monthly', "public/reports/admin/$year/{$year}-{$month}_all_shops.csv");
            $this->generateCombinedReport('yearly', "public/reports/admin/{$year}_all_shops.csv");

            Log::info('All combined reports generated successfully', [
                'date' => $today->toDateString(),
            ]);

            $this->info('Combined reports generated successfully.');
        } catch (\Exception $e) {
            Log::error('Error generating combined reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Error generating combined reports: ' . $e->getMessage());
        }
    }

    private function generateCombinedReport($reportType, $filePath)
    {
        try {
            // Build query for all settled orders
            $query = Order::where('settled_status', 'settled');

            if ($reportType === 'daily') {
                $query->whereDate('settled_at', Carbon::today());
            } elseif ($reportType === 'monthly') {
                $query->whereMonth('settled_at', Carbon::now()->month)
                      ->whereYear('settled_at', Carbon::now()->year);
            } elseif ($reportType === 'yearly') {
                $query->whereYear('settled_at', Carbon::now()->year);
            } else {
                throw new \Exception('Invalid report type');
            }

            // Calculate total revenue and orders
            $totalRevenue = $query->sum('total');
            $totalOrders = $query->count();
            $orders = $query->with(['items.product', 'items.productVariant', 'seller'])->get();

            // Generate CSV content
            $csvData = [];
            $csvData[] = ['Report Type', $reportType];
            $csvData[] = ['Total Revenue', number_format($totalRevenue, 2, '.', '')];
            $csvData[] = ['Total Orders', $totalOrders];
            $csvData[] = [];
            $csvData[] = ['Order ID', 'Shop Name', 'Date', 'Total', 'Items'];

            foreach ($orders as $order) {
                // Get shop name via seller_id
                $shop = Shop::where('owner_id', $order->seller_id)->first();
                $shopName = $shop ? ($shop->shop_name ?? 'Unknown Shop') : 'Unknown Shop';
                $items = $order->items->map(function ($item) {
                    return $item->product->name . ' (' . $item->quantity . ')';
                })->implode(', ');
                $csvData[] = [
                    $order->id,
                    $shopName,
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
                'shop_name' => 'All Shops',
                'file_url' => Storage::url($filePath),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            Log::info('Combined report generated', [
                'report_type' => $reportType,
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating combined report', [
                'report_type' => $reportType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}