<?php
// app/Models/ShopShippingPartner.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopShippingPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'shipping_partner_id'
    ];
}
