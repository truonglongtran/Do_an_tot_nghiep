<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\PlatformVoucher;
use App\Models\ShopVoucher;
use App\Models\ShippingVoucher;
use App\Models\ShippingVoucherPartner;
use App\Models\ProductVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    public function index()
    {
        return response()->json(
            Voucher::with([
                'platformVoucher',
                'shopVoucher.shop',
                'shippingVoucher.shippingPartners.shippingPartner',
                'products.product'
            ])->get()
        );
    }

    public function show($id)
    {
        $voucher = Voucher::with([
            'platformVoucher',
            'shopVoucher.shop',
            'shippingVoucher.shippingPartners.shippingPartner',
            'products.product'
        ])->findOrFail($id);
        return response()->json($voucher);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:vouchers,code',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'min_order_amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'usage_limit' => 'required|integer|min:0',
                'voucher_type' => 'required|in:platform,shop,shipping,product',
                'shop_ids' => 'nullable|array',
                'shop_ids.*' => 'integer|exists:shops,id',
                'shop_ids' => 'required_if:voucher_type,shop|array|min:1',
                'shipping_only' => 'nullable|required_if:voucher_type,shipping|boolean',
                'shipping_partner_ids' => 'nullable|array',
                'shipping_partner_ids.*' => 'integer|exists:shipping_partners,id',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'integer|exists:products,id',
                'product_ids' => 'required_if:voucher_type,product|array|min:1',
            ]);

            \Log::info('Validated data: ' . json_encode($validated));

            return DB::transaction(function () use ($validated) {
                $voucher = Voucher::create([
                    'code' => $validated['code'],
                    'discount_type' => $validated['discount_type'],
                    'discount_value' => $validated['discount_value'],
                    'min_order_amount' => $validated['min_order_amount'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'usage_limit' => $validated['usage_limit'],
                    'used_count' => 0,
                    'voucher_type' => $validated['voucher_type'],
                ]);

                \Log::info('Created voucher: ' . json_encode($voucher));

                if ($validated['voucher_type'] === 'platform') {
                    PlatformVoucher::create(['voucher_id' => $voucher->id]);
                } elseif ($validated['voucher_type'] === 'shop') {
                    if (!empty($validated['shop_ids'])) {
                        foreach ($validated['shop_ids'] as $shop_id) {
                            if (!is_int($shop_id)) {
                                \Log::error('Invalid shop_id type: ' . json_encode($shop_id));
                                throw new \Exception('shop_ids phải chứa các số nguyên');
                            }
                        }
                        $voucher->shopVoucher()->createMany(
                            array_map(fn($shop_id) => ['shop_id' => $shop_id], $validated['shop_ids'])
                        );
                    } else {
                        \Log::warning('No shop_ids provided for shop voucher');
                        throw new \Exception('Vui lòng chọn ít nhất một cửa hàng');
                    }
                } elseif ($validated['voucher_type'] === 'shipping') {
                    $shippingVoucher = ShippingVoucher::create([
                        'voucher_id' => $voucher->id,
                        'shipping_only' => $validated['shipping_only'],
                    ]);
                    if (!empty($validated['shipping_partner_ids'])) {
                        foreach ($validated['shipping_partner_ids'] as $partner_id) {
                            if (!is_int($partner_id)) {
                                \Log::error('Invalid shipping_partner_id type: ' . json_encode($partner_id));
                                throw new \Exception('shipping_partner_ids phải chứa các số nguyên');
                            }
                        }
                        $shippingVoucher->shippingPartners()->createMany(
                            array_map(fn($partner_id) => ['shipping_partner_id' => $partner_id], $validated['shipping_partner_ids'])
                        );
                    }
                } elseif ($validated['voucher_type'] === 'product') {
                    if (!empty($validated['product_ids'])) {
                        foreach ($validated['product_ids'] as $product_id) {
                            if (!is_int($product_id)) {
                                \Log::error('Invalid product_id type: ' . json_encode($product_id));
                                throw new \Exception('product_ids phải chứa các số nguyên');
                            }
                        }
                        $voucher->products()->createMany(
                            array_map(fn($product_id) => ['product_id' => $product_id], $validated['product_ids'])
                        );
                    } else {
                        \Log::warning('No product_ids provided for product voucher');
                        throw new \Exception('Vui lòng chọn ít nhất một sản phẩm');
                    }
                }

                return response()->json($voucher->load([
                    'platformVoucher',
                    'shopVoucher.shop',
                    'shippingVoucher.shippingPartners.shippingPartner',
                    'products.product'
                ]), 201);
            });
        } catch (ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Voucher store error: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $voucher = Voucher::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|unique:vouchers,code,' . $id,
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'min_order_amount' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'usage_limit' => 'required|integer|min:0',
                'voucher_type' => 'required|in:platform,shop,shipping,product',
                'shop_ids' => 'nullable|array',
                'shop_ids.*' => 'integer|exists:shops,id',
                'shop_ids' => 'required_if:voucher_type,shop|array|min:1',
                'shipping_only' => 'nullable|required_if:voucher_type,shipping|boolean',
                'shipping_partner_ids' => 'nullable|array',
                'shipping_partner_ids.*' => 'integer|exists:shipping_partners,id',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'integer|exists:products,id',
                'product_ids' => 'required_if:voucher_type,product|array|min:1',
            ]);

            \Log::info('Validated update data: ' . json_encode($validated));

            return DB::transaction(function () use ($voucher, $validated) {
                $voucher->update([
                    'code' => $validated['code'],
                    'discount_type' => $validated['discount_type'],
                    'discount_value' => $validated['discount_value'],
                    'min_order_amount' => $validated['min_order_amount'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'usage_limit' => $validated['usage_limit'],
                    'voucher_type' => $validated['voucher_type'],
                ]);

                $voucher->platformVoucher()->delete();
                $voucher->shopVoucher()->delete();
                $voucher->shippingVoucher()->delete();
                $voucher->products()->delete();

                if ($validated['voucher_type'] === 'platform') {
                    PlatformVoucher::create(['voucher_id' => $voucher->id]);
                } elseif ($validated['voucher_type'] === 'shop') {
                    if (!empty($validated['shop_ids'])) {
                        foreach ($validated['shop_ids'] as $shop_id) {
                            if (!is_int($shop_id)) {
                                \Log::error('Invalid shop_id type: ' . json_encode($shop_id));
                                throw new \Exception('shop_ids phải chứa các số nguyên');
                            }
                        }
                        $voucher->shopVoucher()->createMany(
                            array_map(fn($shop_id) => ['shop_id' => $shop_id], $validated['shop_ids'])
                        );
                    } else {
                        \Log::warning('No shop_ids provided for shop voucher update');
                        throw new \Exception('Vui lòng chọn ít nhất một cửa hàng');
                    }
                } elseif ($validated['voucher_type'] === 'shipping') {
                    $shippingVoucher = ShippingVoucher::create([
                        'voucher_id' => $voucher->id,
                        'shipping_only' => $validated['shipping_only'],
                    ]);
                    if (!empty($validated['shipping_partner_ids'])) {
                        foreach ($validated['shipping_partner_ids'] as $partner_id) {
                            if (!is_int($partner_id)) {
                                \Log::error('Invalid shipping_partner_id type: ' . json_encode($partner_id));
                                throw new \Exception('shipping_partner_ids phải chứa các số nguyên');
                            }
                        }
                        $shippingVoucher->shippingPartners()->createMany(
                            array_map(fn($partner_id) => ['shipping_partner_id' => $partner_id], $validated['shipping_partner_ids'])
                        );
                    }
                } elseif ($validated['voucher_type'] === 'product') {
                    if (!empty($validated['product_ids'])) {
                        foreach ($validated['product_ids'] as $product_id) {
                            if (!is_int($product_id)) {
                                \Log::error('Invalid product_id type: ' . json_encode($product_id));
                                throw new \Exception('product_ids phải chứa các số nguyên');
                            }
                        }
                        $voucher->products()->createMany(
                            array_map(fn($product_id) => ['product_id' => $product_id], $validated['product_ids'])
                        );
                    } else {
                        \Log::warning('No product_ids provided for product voucher update');
                        throw new \Exception('Vui lòng chọn ít nhất một sản phẩm');
                    }
                }

                return response()->json(['message' => 'Cập nhật voucher thành công', 'voucher' => $voucher->load([
                    'platformVoucher',
                    'shopVoucher.shop',
                    'shippingVoucher.shippingPartners.shippingPartner',
                    'products.product'
                ])]);
            });
        } catch (ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Voucher update error: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->platformVoucher()->delete();
            $voucher->shopVoucher()->delete();
            $voucher->shippingVoucher()->delete();
            $voucher->products()->delete();
            $voucher->delete();

            return response()->json(['message' => 'Voucher đã được xóa']);
        } catch (ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Voucher delete error: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }
}