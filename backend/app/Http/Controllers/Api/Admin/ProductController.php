<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with(['variants' => function ($query) {
                $query->whereNull('deleted_at');
            }])->whereNull('deleted_at');

            // Search by name
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Filter by status
            if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'banned'])) {
                $query->where('status', $request->status);
            }

            $products = $query->get();

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with(['variants' => function ($query) {
                $query->whereNull('deleted_at');
            }])->whereNull('deleted_at')->findOrFail($id);
            return response()->json($product);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Product not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::show: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,approved,banned',
            ]);

            $product = Product::whereNull('deleted_at')->findOrFail($id);
            $product->status = $request->status;
            $product->save();

            return response()->json(['message' => 'Product status updated successfully', 'product' => $product]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Product not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::updateStatus: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function updateVariantStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive',
            ]);

            $variant = ProductVariant::whereNull('deleted_at')->findOrFail($id);
            $variant->status = $request->status;
            $variant->save();

            return response()->json(['message' => 'Variant status updated successfully', 'variant' => $variant]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Variant not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::updateVariantStatus: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::whereNull('deleted_at')->findOrFail($id);
            $product->delete();
            return response()->json(['message' => 'Product and its variants deleted successfully'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Product not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::destroy: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}