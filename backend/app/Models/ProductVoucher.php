<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductVoucher extends Model
{
    protected $table = 'product_vouchers';
    protected $fillable = ['voucher_id', 'product_id'];
    public function voucher() { return $this->belongsTo(Voucher::class); }
    public function product() { return $this->belongsTo(Product::class); }
}