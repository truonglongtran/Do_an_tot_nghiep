<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'shop_name',
        'status',
        'avatar_url',
        'cover_image_url',
        'pickup_address',
        'ward',
        'district',
        'city',
        'phone_number',
    ];

    protected $appends = ['review_count'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shippingPartners()
    {
        return $this->belongsToMany(ShippingPartner::class, 'shop_shipping_partners');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, Product::class);
    }

    public function bannerPlacements()
    {
        return $this->hasMany(BannerPlacement::class);
    }

    public function shopFollowers()
    {
        return $this->hasMany(ShopFollower::class, 'shop_id');
    }

    public function getReviewCountAttribute()
    {
        return \DB::table('reviews')
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->where('products.shop_id', $this->id)
            ->count();
    }
}