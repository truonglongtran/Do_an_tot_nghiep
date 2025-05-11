<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,HasFactory;

    protected $fillable = ['email', 'password', 'role', 'status'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class, 'owner_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'buyer_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'buyer_id');
    }
    public function buyerAddresses()
    {
        return $this->hasMany(BuyerAddress::class);
    }

    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class);
    }
}
