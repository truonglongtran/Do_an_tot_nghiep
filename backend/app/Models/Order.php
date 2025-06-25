<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'buyer_id', 'seller_id', 'address_id', 'settled_status', 'settled_at',
        'shipping_status', 'order_status', 'payment_method', 'subtotal',
        'shipping_fee', 'voucher_id', 'shipping_voucher_id', 'total_discount',
        'total', 'shipping_partner_id', 'tracking_code',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function address()
    {
        return $this->belongsTo(BuyerAddress::class, 'address_id');
    }

    public function shippingPartner()
    {
        return $this->belongsTo(ShippingPartner::class, 'shipping_partner_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function shippingVoucher()
    {
        return $this->belongsTo(Voucher::class, 'shipping_voucher_id');
    }
}