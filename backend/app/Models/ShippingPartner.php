<?php

// app/Models/ShippingPartner.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'api_url', 'status'
    ];

    // Mối quan hệ với Shop
   public function shops()
    {
        return $this->belongsToMany(Shop::class, 'shop_shipping_partners');
    }

}