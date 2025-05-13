<?php

// app/Models/Shop.php
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
        'avatar_url',
        'cover_image_url',
        'pickup_address',
        'ward',
        'district',
        'city',
        'phone_number',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Mối quan hệ với ShippingPartner
  public function shippingPartners()
    {
        return $this->belongsToMany(ShippingPartner::class, 'shop_shipping_partners');
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
