<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'shop_name',
        'pickup_address',
        'ward',
        'district',
        'city',
        'phone_number',
        'is_verified',
        'status',
        'avatar_url',
        'cover_image_url',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shippingPartners()
    {
        return $this->belongsToMany(ShippingPartner::class, 'shop_shipping_partners');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
