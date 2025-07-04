<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\ShippingPartner;
use App\Models\ShopShippingPartner;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShippingPartnerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sellerId = $request->user()->id;

            // Kiểm tra xem seller có shop không
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                Log::error('Shop not found for seller', ['seller_id' => $sellerId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            // Sử dụng JOIN để lấy danh sách đơn vị vận chuyển và trạng thái sử dụng
            $shippingPartners = ShippingPartner::leftJoin('shop_shipping_partners', function ($join) use ($shop) {
                    $join->on('shipping_partners.id', '=', 'shop_shipping_partners.shipping_partner_id')
                         ->where('shop_shipping_partners.shop_id', '=', $shop->id);
                })
                ->select(
                    'shipping_partners.id',
                    'shipping_partners.name',
                    'shipping_partners.api_url',
                    'shipping_partners.status',
                    DB::raw('IF(shop_shipping_partners.id IS NOT NULL, true, false) as is_used')
                )
                ->get();

            Log::info('Shipping partners fetched', [
                'seller_id' => $sellerId,
                'shop_id' => $shop->id,
                'partners_count' => $shippingPartners->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $shippingPartners,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching shipping partners', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đơn vị vận chuyển: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'shipping_partner_id' => 'required|exists:shipping_partners,id',
            ]);

            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();
            if (!$shop) {
                Log::error('Shop not found for seller', ['seller_id' => $sellerId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop của người bán',
                ], 404);
            }

            $shippingPartnerId = $request->input('shipping_partner_id');
            $exists = ShopShippingPartner::where('shop_id', $shop->id)
                ->where('shipping_partner_id', $shippingPartnerId)
                ->exists();

            if ($exists) {
                // Xóa bản ghi nếu đã sử dụng
                ShopShippingPartner::where('shop_id', $shop->id)
                    ->where('shipping_partner_id', $shippingPartnerId)
                    ->delete();
                $action = 'disabled';
                $isUsed = false;
            } else {
                // Thêm bản ghi nếu chưa sử dụng
                ShopShippingPartner::create([
                    'shop_id' => $shop->id,
                    'shipping_partner_id' => $shippingPartnerId,
                ]);
                $action = 'enabled';
                $isUsed = true;
            }

            Log::info('Shipping partner toggled', [
                'seller_id' => $sellerId,
                'shop_id' => $shop->id,
                'shipping_partner_id' => $shippingPartnerId,
                'action' => $action,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn vị vận chuyển thành công',
                'is_used' => $isUsed,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling shipping partner', [
                'seller_id' => $request->user()->id,
                'shipping_partner_id' => $request->input('shipping_partner_id'),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái đơn vị vận chuyển: ' . $e->getMessage(),
            ], 500);
        }
    }
}