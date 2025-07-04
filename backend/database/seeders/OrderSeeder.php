<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Voucher;
use App\Models\ShippingPartner;
use App\Models\BuyerAddress;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $buyerIds = DB::table('users')->whereIn('email', ['buyer1@example.com', 'buyer2@example.com'])->pluck('id')->toArray();
        $sellerIds = DB::table('users')->whereIn('email', ['seller1@example.com', 'seller2@example.com'])->pluck('id')->toArray();
        $voucherIds = Voucher::whereIn('voucher_type', ['platform', 'shop', 'product'])->pluck('id')->toArray();
        $shippingVoucherIds = Voucher::where('voucher_type', 'shipping')->pluck('id')->toArray();
        $shippingPartnerIds = ShippingPartner::where('status', 'active')->pluck('id')->toArray();
        $addressIds = BuyerAddress::pluck('id')->toArray();

        if (empty($buyerIds) || empty($sellerIds) || empty($addressIds)) {
            Log::warning('No buyers, sellers, or addresses found');
            return;
        }

        if (empty($voucherIds)) {
            Log::warning('No platform/shop/product vouchers found');
        }

        if (empty($shippingVoucherIds)) {
            Log::warning('No shipping vouchers found');
        }

        if (empty($shippingPartnerIds)) {
            Log::warning('No active shipping partners found');
        }

        $orders = [];
        for ($i = 0; $i < 10; $i++) {
            $buyer_id = $buyerIds[array_rand($buyerIds)];
            $seller_id = $sellerIds[array_rand($sellerIds)];
            $address_id = $addressIds[array_rand($addressIds)];
            $order_status = ['pending', 'paid', 'canceled'][rand(0, 2)];
            $settled_status = $order_status === 'paid' ? 'settled' : 'unsettled';
            $settled_at = $settled_status === 'settled' ? now() : null;
            $shipping_status = ['pending', 'processing', 'shipping', 'delivered'][rand(0, 3)];
            $voucher_id = !empty($voucherIds) && rand(0, 1) ? $voucherIds[array_rand($voucherIds)] : null;
            $shipping_voucher_id = !empty($shippingVoucherIds) && rand(0, 1) ? $shippingVoucherIds[array_rand($shippingVoucherIds)] : null;
            $shipping_partner_id = !empty($shippingPartnerIds) && $shipping_status !== 'pending' ? $shippingPartnerIds[array_rand($shippingPartnerIds)] : null;
            $tracking_code = $shipping_status === 'shipping' || $shipping_status === 'delivered' ? 'TRK' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) : null;

            $subtotal = rand(100000, 1000000);
            $shipping_fee = 15000;
            $total_discount = ($voucher_id || $shipping_voucher_id) ? rand(10000, 50000) : 0;
            $total = $subtotal + $shipping_fee - $total_discount;

            $orders[] = [
                'buyer_id' => $buyer_id,
                'seller_id' => $seller_id,
                'address_id' => $address_id,
                'settled_status' => $settled_status,
                'settled_at' => $settled_at,
                'shipping_status' => $shipping_status,
                'order_status' => $order_status,
                'payment_method' => 'COD',
                'subtotal' => $subtotal,
                'shipping_fee' => $shipping_fee,
                'voucher_id' => $voucher_id,
                'shipping_voucher_id' => $shipping_voucher_id,
                'total_discount' => $total_discount,
                'total' => $total,
                'shipping_partner_id' => $shipping_partner_id,
                'tracking_code' => $tracking_code,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('orders')->insert($orders);
    }
}