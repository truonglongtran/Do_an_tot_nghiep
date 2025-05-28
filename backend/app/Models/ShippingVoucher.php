<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShippingVoucher extends Model
{
    protected $fillable = ['voucher_id', 'shipping_only'];
    public function voucher() { return $this->belongsTo(Voucher::class); }
    public function shippingPartners() { return $this->hasMany(ShippingVoucherPartner::class); }
}