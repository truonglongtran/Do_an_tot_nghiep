<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                    'file_url' => $report->file_url,
                    'created_at' => $report->created_at->toIso8601String(),
                ];
            });

            // Get available report types for filters
            $reportTypes = Report::distinct('report_type')->pluck('report_type')->toArray();

            return response()->json([
                'reports' => $reports,
                'report_types' => $reportTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to fetch reports'], 500);
        }
    }
}