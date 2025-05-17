<?php

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'settled_status',
        'settled_at',
        'shipping_status',
        'order_status',
    ];

    protected $appends = ['total_amount']; // Ensure total_amount is included in JSON

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class)->with('productVariant');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function getTotalAmountAttribute()
    {
        if ($this->items->isEmpty()) {
            Log::warning('No items found for order', ['order_id' => $this->id]);
            return 0;
        }

        $total = $this->items->sum(function ($item) {
            if (!$item->productVariant) {
                Log::warning('ProductVariant not found for OrderItem', [
                    'order_id' => $this->id,
                    'item_id' => $item->id,
                    'variant_id' => $item->product_variant_id,
                ]);
                return 0;
            }
            $price = is_numeric($item->productVariant->price)
                ? floatval($item->productVariant->price)
                : 0;
            Log::debug('OrderItem Total', [
                'order_id' => $this->id,
                'item_id' => $item->id,
                'variant_id' => $item->product_variant_id,
                'price' => $price,
                'quantity' => $item->quantity,
                'subtotal' => $price * $item->quantity,
            ]);
            return $price * $item->quantity;
        });

        Log::info('Order Total Amount', ['order_id' => $this->id, 'total' => $total, 'item_count' => $this->items->count()]);
        return $total;
    }
}

