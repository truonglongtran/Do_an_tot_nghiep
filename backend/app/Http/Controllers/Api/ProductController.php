<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with('variants');

            // Tìm kiếm theo tên
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Lọc theo trạng thái
            if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'banned'])) {
                $query->where('status', $request->status);
            }

            $products = $query->get();

            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('Lỗi trong AdminProductController::index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi server'], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with('variants')->findOrFail($id);
            return response()->json($product);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi trong AdminProductController::show: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi server'], 500);
        }
    }
}