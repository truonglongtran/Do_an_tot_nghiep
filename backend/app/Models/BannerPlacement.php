<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerPlacement extends Model
{
    protected $fillable = [
        'banner_id',
        'location_id',
        'shop_id',
        'display_order',
        'is_active',
    ];

    // Quan hệ ngược về banner
    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    // Quan hệ ngược về location
    public function location(): BelongsTo
    {
        return $this->belongsTo(BannerDisplayLocation::class, 'location_id');
    }

    // Nếu có model Shop, quan hệ về shop
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
