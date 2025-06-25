<?php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\BuyerAddress;
use App\Models\ShippingPartner;
use App\Models\Shop;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('buyer_id', $user->id)
            ->with([
                'items' => function ($q) {
                    $q->with([
                        'product' => function ($q) {
                            $q->select('id', 'shop_id', 'name');
                        },
                        'product.shop' => function ($q) {
                            $q->select('id', 'shop_name', 'owner_id');
                        },
                        'productVariant' => function ($q) {
                            $q->select('id', 'product_id', 'price', 'image_url');
                        }
                    ]);
                },
                'shippingPartner' => function ($q) {
                    $q->select('id', 'name');
                },
                'voucher' => function ($q) {
                    $q->select('id', 'code', 'discount_type', 'discount_value');
                },
                'shippingVoucher' => function ($q) {
                    $q->select('id', 'code', 'discount_type', 'discount_value');
                }
            ])
            ->select('id', 'seller_id', 'shipping_partner_id', 'voucher_id', 'shipping_voucher_id', 'order_status', 'shipping_status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json(['orders' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::where('id', $id)
            ->where('buyer_id', $user->id)
            ->with([
                'items' => function ($q) {
                    $q->with([
                        'product' => function ($q) {
                            $q->select('id', 'shop_id', 'name');
                        },
                        'product.shop' => function ($q) {
                            $q->select('id', 'shop_name', 'owner_id');
                        },
                        'productVariant' => function ($q) {
                            $q->select('id', 'product_id', 'price', 'image_url');
                        }
                    ]);
                },
                'shippingPartner' => function ($q) {
                    $q->select('id', 'name');
                },
                'voucher' => function ($q) {
                    $q->select('id', 'code', 'discount_type', 'discount_value');
                },
                'shippingVoucher' => function ($q) {
                    $q->select('id', 'code', 'discount_type', 'discount_value');
                }
            ])
            ->firstOrFail();

        return response()->json(['order' => $order]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'cart_ids' => 'required|array|min:1',
            'cart_ids.*' => 'exists:carts,id',
            'address_id' => 'required|exists:buyer_addresses,id',
            'shipping_method_id' => 'required|exists:shipping_partners,id',
            'payment_method' => 'required|in:COD',
            'shipping_voucher_id' => 'nullable|exists:vouchers,id',
            'product_voucher_id' => 'nullable|exists:vouchers,id',
        ]);

        try {
            DB::beginTransaction();

            $buyerId = auth()->id();
            $cartIds = $request->cart_ids;

            // Validate address belongs to the buyer
            $address = BuyerAddress::where('id', $request->address_id)
                ->where('user_id', $buyerId)
                ->first();
            if (!$address) {
                return response()->json(['error' => 'Địa chỉ không hợp lệ hoặc không thuộc về bạn.'], 400);
            }

            $carts = Cart::whereIn('id', $cartIds)
                ->where('user_id', $buyerId)
                ->with([
                    'productVariant' => function ($q) {
                        $q->select('id', 'product_id', 'price', 'image_url');
                    },
                    'productVariant.product' => function ($q) {
                        $q->select('id', 'shop_id');
                    },
                    'productVariant.product.shop' => function ($q) {
                        $q->select('id', 'owner_id');
                    }
                ])
                ->get();

            if ($carts->isEmpty()) {
                return response()->json(['error' => 'Giỏ hàng không tồn tại hoặc không thuộc về bạn.'], 400);
            }

            $ownerIds = $carts->pluck('productVariant.product.shop.owner_id')->unique();
            if ($ownerIds->count() !== 1) {
                return response()->json(['error' => 'Tất cả sản phẩm phải thuộc cùng một người bán.'], 400);
            }
            $sellerId = $ownerIds->first();

            $order = new Order();
            $order->buyer_id = $buyerId;
            $order->seller_id = $sellerId;
            $order->address_id = $request->address_id;
            $order->shipping_partner_id = $request->shipping_method_id;
            $order->payment_method = $request->payment_method;

            $totalPrice = $carts->sum(function ($cart) {
                return $cart->productVariant->price * $cart->quantity;
            });

            $shippingMethod = ShippingPartner::findOrFail($request->shipping_method_id);
            $shop = Shop::where('owner_id', $sellerId)->first();
            $shippingPrice = $shop ? 15000 : 15000; // Default price since cost is not in shop_shipping_partners

            $totalDiscount = 0;

            if ($request->shipping_voucher_id) {
                $voucher = Voucher::findOrFail($request->shipping_voucher_id);
                if ($voucher->voucher_type === 'shipping') {
                    $totalDiscount += $voucher->discount_type === 'fixed'
                        ? min($shippingPrice, $voucher->discount_value)
                        : ($shippingPrice * $voucher->discount_value) / 100;
                    $order->shipping_voucher_id = $voucher->id;
                }
            }

            if ($request->product_voucher_id) {
                $voucher = Voucher::findOrFail($request->product_voucher_id);
                if (in_array($voucher->voucher_type, ['platform', 'shop', 'product'])) {
                    $totalDiscount += $voucher->discount_type === 'fixed'
                        ? $voucher->discount_value
                        : ($totalPrice * $voucher->discount_value) / 100;
                    $order->voucher_id = $voucher->id;
                }
            }

            $order->subtotal = $totalPrice;
            $order->shipping_fee = $shippingPrice;
            $order->total_discount = $totalDiscount;
            $order->total = max(0, $totalPrice + $shippingPrice - $totalDiscount);
            $order->order_status = 'pending';
            $order->save();

            foreach ($carts as $cart) {
                $order->items()->create([
                    'product_id' => $cart->productVariant->product_id,
                    'product_variant_id' => $cart->product_variant_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->productVariant->price,
                ]);
            }

            Cart::whereIn('id', $cartIds)->delete();

            DB::commit();

            return response()->json(['message' => 'Đơn hàng đã được tạo thành công.', 'order' => $order], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi tạo đơn hàng: ' . $e->getMessage()], 500);
        }
    }
}