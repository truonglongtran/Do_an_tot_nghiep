<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerProfile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'shop_name', 'pickup_address',
        'ward', 'district', 'city', 'phone_number', 'is_verified'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

