<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShopVoucher extends Model
{
    protected $fillable = ['voucher_id', 'shop_id'];
    public function voucher() { return $this->belongsTo(Voucher::class); }
    public function shop() { return $this->belongsTo(Shop::class); }
}