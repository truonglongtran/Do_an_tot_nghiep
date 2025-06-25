<?php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\Shop;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function available(Request $request)
    {
        try {
            $request->validate([
                'owner_id' => 'required|exists:users,id',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'exists:products,id',
                'subtotal' => 'nullable|numeric|min:0',
            ], [
                'owner_id.required' => 'The owner id field is required.',
                'owner_id.exists' => 'The selected owner id is invalid.',
                'product_ids.*.exists' => 'One or more product IDs are invalid.',
                'subtotal.numeric' => 'The subtotal must be a number.',
                'subtotal.min' => 'The subtotal must be at least 0.',
            ]);

            $ownerId = $request->query('owner_id');
            $productIds = $request->query('product_ids', []);
            $subtotal = $request->query('subtotal', 0);

            // Find the shop associated with the owner_id
            $shop = Shop::where('owner_id', $ownerId)->first();

            if (!$shop) {
                return response()->json(['error' => 'No shop found for the provided owner ID.'], 404);
            }

            // Retrieve shipping vouchers
            $shippingVouchers = Voucher::where('voucher_type', 'shipping')
                ->whereHas('shopVoucher', function ($query) use ($shop) {
                    $query->where('shop_id', $shop->id);
                })
                ->where('min_order_amount', '<=', $subtotal)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereColumn('used_count', '<', 'usage_limit')
                ->get(['id', 'code', 'discount_type', 'discount_value']);

            // Retrieve product vouchers
            $productVouchers = Voucher::whereIn('voucher_type', ['platform', 'shop', 'product'])
                ->whereHas('shopVoucher', function ($query) use ($shop) {
                    $query->where('shop_id', $shop->id);
                })
                ->where('min_order_amount', '<=', $subtotal)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereColumn('used_count', '<', 'usage_limit')
                ->when(!empty($productIds), function ($query) use ($productIds) {
                    $query->whereHas('products', function ($q) use ($productIds) {
                        $q->whereIn('product_vouchers.product_id', $productIds);
                    });
                })
                ->get(['id', 'code', 'discount_type', 'discount_value']);

            return response()->json([
                'shipping_vouchers' => $shippingVouchers,
                'product_vouchers' => $productVouchers,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Lỗi VoucherController@available: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching vouchers.'], 500);
        }
    }
}