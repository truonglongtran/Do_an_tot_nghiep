<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerAddress extends Model
{
    protected $fillable = [
        'user_id', 'recipient_name', 'phone_number', 'address_line',
        'ward', 'district', 'city', 'is_default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

