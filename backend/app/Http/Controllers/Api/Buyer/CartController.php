<?php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Cart::where('user_id', Auth::id())
                ->whereNull('deleted_at')
                ->with([
                    'productVariant' => function ($query) {
                        $query->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status')
                            ->where('status', 'active')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    },
                    'product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    }
                ]);

            // Filter by cart_ids if provided
            if ($request->has('cart_ids')) {
                $cartIds = is_array($request->cart_ids) ? $request->cart_ids : explode(',', $request->cart_ids);
                $query->whereIn('id', $cartIds);
            }

            $carts = $query->get();

            return response()->json([
                'carts' => $carts->map(fn($cart) => [
                    'id' => $cart->id,
                    'product_variant' => $cart->productVariant ? [
                        'id' => $cart->productVariant->id,
                        'sku' => $cart->productVariant->sku,
                        'price' => $cart->productVariant->price,
                        'stock' => $cart->productVariant->stock,
                        'image_url' => $cart->productVariant->image_url ?? 'https://via.placeholder.com/100',
                        'status' => $cart->productVariant->status,
                        'product' => $cart->productVariant->product ? [
                            'id' => $cart->productVariant->product->id,
                            'name' => $cart->productVariant->product->name,
                            'shop' => $cart->productVariant->product->shop ? [
                                'id' => $cart->productVariant->product->shop->id,
                                'shop_name' => $cart->productVariant->product->shop->shop_name,
                                'owner_id' => $cart->productVariant->product->shop->owner_id,
                            ] : null,
                        ] : null,
                    ] : null,
                    'product' => $cart->product ? [
                        'id' => $cart->product->id,
                        'name' => $cart->product->name,
                        'shop' => $cart->product->shop ? [
                            'id' => $cart->product->shop->id,
                            'shop_name' => $cart->product->shop->shop_name,
                            'owner_id' => $cart->product->shop->owner_id,
                        ] : null,
                    ] : null,
                    'quantity' => $cart->quantity,
                ]),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Lỗi CartController@index: ' . $e->getMessage() . ' | User ID: ' . auth()->id());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Rest of the CartController (add, update, destroy, count) remains unchanged
    public function add(Request $request)
    {
        try {
            $request->validate([
                'product_variant_id' => 'required_without:product_id|exists:product_variants,id',
                'product_id' => 'required_without:product_variant_id|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ], [
                'product_variant_id.exists' => 'Biến thể không tồn tại.',
                'product_id.exists' => 'Sản phẩm không tồn tại.',
                'quantity.required' => 'Số lượng là bắt buộc.',
                'quantity.integer' => 'Số lượng phải là số nguyên.',
                'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
            ]);

            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            if ($request->has('product_variant_id')) {
                $existingCart = Cart::where('user_id', $user->id)
                    ->where('product_variant_id', $request->product_variant_id)
                    ->whereNull('deleted_at')
                    ->first();

                $variant = ProductVariant::where('id', $request->product_variant_id)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->first();

                if (!$variant) {
                    return response()->json(['error' => 'Biến thể không khả dụng hoặc hết hàng'], 400);
                }

                $newQuantity = $existingCart ? $existingCart->quantity + $request->quantity : $request->quantity;

                if ($variant->stock < $newQuantity) {
                    \Log::warning('Requested quantity exceeds stock: product_variant_id=' . $request->product_variant_id . ', quantity=' . $newQuantity . ', stock=' . $variant->stock);
                    return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
                }

                if ($existingCart) {
                    $existingCart->update(['quantity' => $newQuantity]);
                    $cart = $existingCart;
                } else {
                    $cart = Cart::create([
                        'user_id' => $user->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $request->quantity,
                    ]);
                }
            } else {
                $existingCart = Cart::where('user_id', $user->id)
                    ->where('product_id', $request->product_id)
                    ->whereNull('deleted_at')
                    ->first();

                $product = Product::where('id', $request->product_id)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$product) {
                    return response()->json(['error' => 'Sản phẩm không khả dụng'], 400);
                }

                $newQuantity = $existingCart ? $existingCart->quantity + $request->quantity : $request->quantity;

                if ($existingCart) {
                    $existingCart->update(['quantity' => $newQuantity]);
                    $cart = $existingCart;
                } else {
                    $cart = Cart::create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'quantity' => $request->quantity,
                    ]);
                }
            }

            $updatedCart = Cart::where('id', $cart->id)
                ->with([
                    'productVariant' => function ($query) {
                        $query->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status')
                            ->where('status', 'active')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    },
                    'product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    }
                ])
                ->first();

            return response()->json([
                'message' => 'Thêm vào giỏ hàng thành công',
                'cart' => [
                    'id' => $updatedCart->id,
                    'product_variant' => $updatedCart->productVariant ? [
                        'id' => $updatedCart->productVariant->id,
                        'sku' => $updatedCart->productVariant->sku,
                        'price' => $updatedCart->productVariant->price,
                        'stock' => $updatedCart->productVariant->stock,
                        'image_url' => $updatedCart->productVariant->image_url ?? 'https://via.placeholder.com/100',
                        'status' => $updatedCart->productVariant->status,
                        'product' => $updatedCart->productVariant->product ? [
                            'id' => $updatedCart->productVariant->product->id,
                            'name' => $updatedCart->productVariant->product->name,
                            'shop' => $updatedCart->productVariant->product->shop ? [
                                'id' => $updatedCart->productVariant->product->shop->id,
                                'shop_name' => $updatedCart->productVariant->product->shop->shop_name,
                                'owner_id' => $updatedCart->productVariant->product->shop->owner_id,
                            ] : null,
                        ] : null,
                    ] : null,
                    'product' => $updatedCart->product ? [
                        'id' => $updatedCart->product->id,
                        'name' => $updatedCart->product->name,
                        'shop' => $updatedCart->product->shop ? [
                            'id' => $updatedCart->product->shop->id,
                            'shop_name' => $updatedCart->product->shop->shop_name,
                            'owner_id' => $updatedCart->product->shop->owner_id,
                        ] : null,
                    ] : null,
                    'quantity' => $updatedCart->quantity,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            \Log::error('Lỗi CartController@add: ' . $e->getMessage() . ' | User ID: ' . auth()->id());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $cart = Cart::where('id', $id)
                ->where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->with([
                    'productVariant' => function ($query) {
                        $query->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status')
                            ->where('status', 'active')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    },
                    'product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    }
                ])
                ->firstOrFail();

            $request->validate([
                'quantity' => 'required|integer|min:1',
            ], [
                'quantity.required' => 'Số lượng là bắt buộc.',
                'quantity.integer' => 'Số lượng phải là số nguyên.',
                'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
            ]);

            if ($cart->product_variant_id) {
                $variant = $cart->productVariant;
                if (!$variant) {
                    \Log::warning('Cart item has no associated variant: cart_id=' . $cart->id);
                    return response()->json(['error' => 'Biến thể không tồn tại'], 400);
                }
                if ($variant->status !== 'active') {
                    \Log::warning('Cart item variant is not active: variant_id=' . $variant->id);
                    return response()->json(['error' => 'Biến thể không còn hoạt động'], 400);
                }
                if ($variant->stock < $request->quantity) {
                    \Log::warning('Requested quantity exceeds stock: cart_id=' . $cart->id . ', quantity=' . $request->quantity . ', stock=' . $variant->stock);
                    return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
                }
            }

            $cart->update(['quantity' => $request->quantity]);

            $updatedCart = Cart::where('id', $id)
                ->with([
                    'productVariant' => function ($query) {
                        $query->select('id', 'product_id', 'sku', 'price', 'stock', 'image_url', 'status')
                            ->where('status', 'active')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'productVariant.product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    },
                    'product' => function ($query) {
                        $query->select('id', 'name', 'shop_id')
                            ->whereNull('deleted_at');
                    },
                    'product.shop' => function ($query) {
                        $query->select('id', 'shop_name', 'owner_id');
                    }
                ])
                ->first();

            return response()->json([
                'message' => 'Cập nhật giỏ hàng thành công',
                'cart' => [
                    'id' => $updatedCart->id,
                    'product_variant' => $updatedCart->productVariant ? [
                        'id' => $updatedCart->productVariant->id,
                        'sku' => $updatedCart->productVariant->sku,
                        'price' => $updatedCart->productVariant->price,
                        'stock' => $updatedCart->productVariant->stock,
                        'image_url' => $updatedCart->productVariant->image_url ?? 'https://via.placeholder.com/100',
                        'status' => $updatedCart->productVariant->status,
                        'product' => $updatedCart->productVariant->product ? [
                            'id' => $updatedCart->productVariant->product->id,
                            'name' => $updatedCart->productVariant->product->name,
                            'shop' => $updatedCart->productVariant->product->shop ? [
                                'id' => $updatedCart->productVariant->product->shop->id,
                                'shop_name' => $updatedCart->productVariant->product->shop->shop_name,
                                'owner_id' => $updatedCart->productVariant->product->shop->owner_id,
                            ] : null,
                        ] : null,
                    ] : null,
                    'product' => $updatedCart->product ? [
                        'id' => $updatedCart->product->id,
                        'name' => $updatedCart->product->name,
                        'shop' => $updatedCart->product->shop ? [
                            'id' => $updatedCart->product->shop->id,
                            'shop_name' => $updatedCart->product->shop->shop_name,
                            'owner_id' => $updatedCart->product->shop->owner_id,
                        ] : null,
                    ] : null,
                    'quantity' => $updatedCart->quantity,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()['quantity'][0]], 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Mục giỏ hàng không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi CartController@update: ' . $e->getMessage() . ' | Cart ID: ' . $id);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cart = Cart::where('id', $id)
                ->where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->firstOrFail();

            $cart->delete();

            return response()->json(['message' => 'Xóa mục giỏ hàng thành công'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Mục giỏ hàng không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi CartController@destroy: ' . $e->getMessage() . ' | Cart ID: ' . $id);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function count(Request $request)
    {
        try {
            $count = Cart::where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->count();

            return response()->json(['count' => $count], 200);
        } catch (\Exception $e) {
            \Log::error('Lỗi CartController@count: ' . $e->getMessage() . ' | User ID: ' . auth()->id());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}