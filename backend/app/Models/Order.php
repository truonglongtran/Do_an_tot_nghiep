<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'total_amount',
        'settled_status',
        'settled_at',
        'shipping_status',
        'order_status',
    ];

    // Quan hệ với bảng User
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Quan hệ với bảng OrderItem
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Quan hệ với bảng Payment
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Quan hệ với bảng Dispute
    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    // Quan hệ với bảng Review
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
