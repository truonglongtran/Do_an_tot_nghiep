<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerAddress extends Model
{
    protected $table = 'buyer_addresses';

    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone_number',
        'address_line',
        'ward',
        'district',
        'city',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}