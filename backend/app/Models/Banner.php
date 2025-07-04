<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'img_url',
        'link_url',
        'start_date',
        'end_date',
    ];

    // 1 banner có thể có nhiều placements
    public function placements(): HasMany
    {
        return $this->hasMany(BannerPlacement::class);
    }
    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'banner_placements', 'banner_id', 'shop_id')
                    ->withPivot('location_id', 'display_order', 'is_active')
                    ->withTimestamps();
    }
}
