<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'phone_number',
        'avatar_url',
        'password',
        'role',
        'status',
    ];

    /**
     * Các cột bị ẩn khi serialize.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Các cột nên được ép kiểu.
     *
     * @var array
     */
    protected $casts = [
        'password' => 'hashed',
        'phone_number' => 'string',
        'avatar_url' => 'string',
    ];

    public function shop()
    {
        return $this->hasOne(Shop::class, 'owner_id');
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

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }

    public function searchHistories()
    {
        return $this->hasMany(SearchHistory::class, 'user_id');
    }

    public function shopFollowers()
    {
        return $this->hasMany(ShopFollower::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class, 'user_id');
    }
}