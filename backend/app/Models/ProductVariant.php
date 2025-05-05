<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'size',
        'sku',
        'price',
        'stock',
        'image_url',
        'status',
    ];

    // Quan hệ với bảng Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
