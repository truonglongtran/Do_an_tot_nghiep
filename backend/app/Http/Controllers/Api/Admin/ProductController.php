<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
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
            Log::info('Fetching products with params:', $request->all());
            $query = Product::with([
                'category' => fn($q) => $q->select('id', 'name'),
                'variants' => fn($q) => $q->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status'),
                'variants.variantAttributes' => fn($q) => $q->select(
                    'product_variant_attributes.id',
                    'product_variant_attributes.product_variant_id',
                    'attributes.name as attribute_name',
                    'attribute_values.value as attribute_value'
                )
                ->join('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                ->join('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id')
            ])->whereNull('deleted_at');

            // Lọc theo search, status, category_id
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('variants', function ($qv) use ($search) {
                          $qv->where('sku', 'like', "%{$search}%");
                      });
                });
            }
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
            if ($categoryId = $request->input('category_id')) {
                $query->where('category_id', $categoryId);
            }

            $products = $query->get()->map(function ($product) {
                $totalSales = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.product_id', $product->id)
                    ->where('orders.order_status', 'paid')
                    ->sum('order_items.quantity');

                // Chuyển đổi images thành URL công khai
                $images = $product->images ? json_decode($product->images, true) : [];
                $images = array_map(fn($path) => $path ? Storage::url($path) : null, $images);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                    'images' => $images,
                    'thumbnail' => !empty($images) ? $images[0] : ($product->variants->first()->image_url ? Storage::url($product->variants->first()->image_url) : null),
                    'price_min' => $product->variants->count() ? $product->variants->min('price') : $product->price,
                    'price_max' => $product->variants->count() ? $product->variants->max('price') : $product->price,
                    'total_stock' => $product->variants->count() ? $product->variants->sum('stock') : $product->stock,
                    'total_sales' => (int) $totalSales,
                    'status' => $product->status,
                    'variants' => $product->variants->map(function ($variant) {
                        $variantSales = DB::table('order_items')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('order_items.product_variant_id', $variant->id)
                            ->where('orders.order_status', 'paid')
                            ->sum('order_items.quantity');

                        return [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'price' => $variant->price,
                            'stock' => $variant->stock,
                            'image_url' => $variant->image_url ? Storage::url($variant->image_url) : null,
                            'status' => $variant->status,
                            'sales' => (int) $variantSales,
                            'attributes' => $variant->variantAttributes->map(function ($attr) {
                                return [
                                    'name' => $attr->attribute_name,
                                    'value' => $attr->attribute_value,
                                ];
                            }),
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with([
                'category' => fn($q) => $q->select('id', 'name'),
                'variants' => fn($q) => $q->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status'),
                'variants.variantAttributes' => fn($q) => $q->select(
                    'product_variant_attributes.id',
                    'product_variant_attributes.product_variant_id',
                    'attributes.name as attribute_name',
                    'attribute_values.value as attribute_value'
                )
                ->join('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                ->join('attribute_values', 'product_variant_attributes.attribute_value_id', '=', 'attribute_values.id')
            ])->whereNull('deleted_at')->findOrFail($id);

            $totalSales = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $product->id)
                ->where('orders.order_status', 'paid')
                ->sum('order_items.quantity');

            // Chuyển đổi images thành URL công khai
            $images = $product->images ? json_decode($product->images, true) : [];
            $images = array_map(fn($path) => $path ? Storage::url($path) : null, $images);

            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                'images' => $images,
                'thumbnail' => !empty($images) ? $images[0] : ($product->variants->first()->image_url ? Storage::url($product->variants->first()->image_url) : null),
                'price_min' => $product->variants->count() ? $product->variants->min('price') : $product->price,
                'price_max' => $product->variants->count() ? $product->variants->max('price') : $product->price,
                'total_stock' => $product->variants->count() ? $product->variants->sum('stock') : $product->stock,
                'total_sales' => (int) $totalSales,
                'status' => $product->status,
                'variants' => $product->variants->map(function ($variant) {
                    $variantSales = DB::table('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->where('order_items.product_variant_id', $variant->id)
                        ->where('orders.order_status', 'paid')
                        ->sum('order_items.quantity');

                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'stock' => $variant->stock,
                        'image_url' => $variant->image_url ? Storage::url($variant->image_url) : null,
                        'status' => $variant->status,
                        'sales' => (int) $variantSales,
                        'attributes' => $variant->variantAttributes->map(function ($attr) {
                            return [
                                'name' => $attr->attribute_name,
                                'value' => $attr->attribute_value,
                            ];
                        }),
                    ];
                }),
            ];

            return response()->json($formattedProduct);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::show: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
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

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái sản phẩm thành công',
                'product' => $product
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::updateStatus: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    public function updateVariantStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive',
            ]);

            $variant = ProductVariant::whereNull('deleted_at')->findOrFail($id);
            $variant->status = $request->input('status');
            $variant->save();

            Log::info('Variant status updated', [
                'variant_id' => $id,
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái biến thể thành công',
                'variant' => [
                    'id' => $variant->id,
                    'status' => $variant->status,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Biến thể không tồn tại'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::updateVariantStatus: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::whereNull('deleted_at')->findOrFail($id);
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm và các biến thể thành công'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        } catch (\Exception $e) {
            Log::error('Error in ProductController::destroy: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }
}