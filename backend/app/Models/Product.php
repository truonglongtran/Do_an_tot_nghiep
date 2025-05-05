<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'price',
        'stock',
        'status',
    ];

    // Quan hệ với bảng Shop
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Quan hệ với bảng ProductVariant
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Quan hệ với bảng OrderItem
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Quan hệ với bảng Review
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
