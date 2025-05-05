<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'start_date',
        'end_date',
        'usage_limited',
        'used_count',
        'voucher_type',
        'shop_id',
        'shipping_only',
        'applicable_shipping_partners',
    ];

    // Quan hệ với bảng Shop
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Quan hệ với bảng VoucherProduct
    public function voucherProducts()
    {
        return $this->hasMany(VoucherProduct::class);
    }
}
