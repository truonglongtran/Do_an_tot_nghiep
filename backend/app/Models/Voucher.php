<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'min_order_amount',
        'start_date', 'end_date', 'usage_limit', 'used_count', 'voucher_type'
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_order_amount' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function platformVoucher() { return $this->hasOne(PlatformVoucher::class); }
    public function shopVoucher() { return $this->hasMany(ShopVoucher::class); }
    public function shippingVoucher() { return $this->hasOne(ShippingVoucher::class); }
    public function products() { return $this->hasMany(ProductVoucher::class); }
}