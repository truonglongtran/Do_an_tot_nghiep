<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct()
    {
        // Apply middleware to ensure only authorized admins can access
        $this->middleware('auth:sanctum');
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !in_array($user->role, ['superadmin', 'admin', 'moderator'])) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        try {
            $query = Product::with([
                'category' => fn($q) => $q->select('id', 'name'),
                'variants.attributes' => fn($q) => $q->select(
                    'product_variant_attributes.id',
                    'product_variant_attributes.product_variant_id',
                    'attributes.name as attribute_name',
                    'attribute_values.value as attribute_value'
                )
                ->leftJoin('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                ->leftJoin('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id'),
                'variants.orderItems' => fn($q) => $q->select(
                    'order_items.product_variant_id',
                    DB::raw('SUM(order_items.quantity) as total_sales')
                )->groupBy('order_items.product_variant_id')
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

            $perPage = $request->input('per_page', 10);
            $products = $query->paginate($perPage);

            // Map products to include price_min, price_max, and total_sales
            $products->getCollection()->transform(function ($product) {
                $product->price_min = $product->variants->min('price') ?? 0;
                $product->price_max = $product->variants->max('price') ?? 0;
                $product->total_sales = $product->variants->sum(function ($variant) {
                    return $variant->orderItems->sum('total_sales') ?? 0;
                });
                return $product;
            });

            return response()->json([
                'data' => $products->items(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]);
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
                'variants.attributes' => fn($q) => $q->select(
                    'product_variant_attributes.id',
                    'product_variant_attributes.product_variant_id',
                    'attributes.name as attribute_name',
                    'attribute_values.value as attribute_value'
                )
                ->leftJoin('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                ->leftJoin('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id'),
                'variants.orderItems' => fn($q) => $q->select(
                    'order_items.product_variant_id',
                    DB::raw('SUM(order_items.quantity) as total_sales')
                )->groupBy('order_items.product_variant_id')
            ])->whereNull('deleted_at')->findOrFail($id);

            $product->price_min = $product->variants->min('price') ?? 0;
            $product->price_max = $product->variants->max('price') ?? 0;
            $product->total_sales = $product->variants->sum(function ($variant) {
                return $variant->orderItems->sum('total_sales') ?? 0;
            });

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

    public function updateProductVariantStatus(Request $request, $id)
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