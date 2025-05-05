<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'shop_name',
        'status',
        'enabled_shipping_partners',
        'avatar_url',
        'cover_image_url',
    ];

    // Quan hệ với bảng User
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Quan hệ với bảng Product
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
