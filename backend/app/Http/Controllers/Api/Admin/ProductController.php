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
            $query = Product::with([
                'category' => fn($q) => $q->select('id', 'name'),
                'variants.attributes' => fn($q) => $q->select('product_variant_attributes.id', 'product_variant_attributes.product_variant_id', 
                    'attributes.name as attribute_name', 'attribute_values.value as attribute_value')
                    ->join('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                    ->join('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id')
            ])->whereNull('deleted_at');

            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'banned'])) {
                $query->where('status', $request->status);
            }
            if ($request->has('category_id') && is_numeric($request->category_id)) {
                $query->where('category_id', $request->category_id);
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
            $product = Product::with([
                'category' => fn($q) => $q->select('id', 'name'),
                'variants.attributes' => fn($q) => $q->select('product_variant_attributes.id', 'product_variant_attributes.product_variant_id', 
                    'attributes.name as attribute_name', 'attribute_values.value as attribute_value')
                    ->join('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                    ->join('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id')
            ])->whereNull('deleted_at')->findOrFail($id);
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