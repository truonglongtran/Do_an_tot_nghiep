<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    protected $fillable = ['user_id', 'points', 'transaction_type', 'order_id'];

    /**
     * Get the user associated with the loyalty points.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the order associated with the loyalty points.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}