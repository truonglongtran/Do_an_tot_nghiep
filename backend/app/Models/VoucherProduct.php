<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'product_id',
    ];

    // Quan hệ với bảng Voucher
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    // Quan hệ với bảng Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
