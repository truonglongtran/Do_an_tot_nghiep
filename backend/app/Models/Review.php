<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'buyer_id',
        'product_id',
        'rating',
        'comment',
        'images',
    ];

    // Quan hệ với bảng Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ với bảng Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Quan hệ với bảng User (Buyer)
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
