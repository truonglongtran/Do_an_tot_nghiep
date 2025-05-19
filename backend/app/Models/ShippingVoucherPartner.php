<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShippingVoucherPartner extends Model
{
    protected $fillable = ['shipping_voucher_id', 'shipping_partner_id'];
    public function shippingVoucher() { return $this->belongsTo(ShippingVoucher::class); }
    public function shippingPartner() { return $this->belongsTo(ShippingPartner::class); }
}