<?php

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

    protected $appends = ['total_amount'];

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
        return $this->hasMany(OrderItem::class); // Loại bỏ ->with('productVariant')
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
        // Đảm bảo items được tải
        if (!$this->relationLoaded('items')) {
            $this->load('items.productVariant');
        }

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
                // Fallback to item price if available
                $price = isset($item->price) && is_numeric($item->price) ? floatval($item->price) : 0;
                return $price * $item->quantity;
            }

            $price = is_numeric($item->productVariant->price) ? floatval($item->productVariant->price) : 0;
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