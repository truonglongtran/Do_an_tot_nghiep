<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
        'role',
        'status',
    ];

    // Quan hệ với bảng Shops
    public function shop()
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }

    // Quan hệ với bảng Orders
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }
}
