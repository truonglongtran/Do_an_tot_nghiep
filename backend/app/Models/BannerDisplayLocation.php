<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerDisplayLocation extends Model
{
    protected $table = 'banner_display_locations';

    protected $fillable = [
        'location_name',
        'code',
        'description',
        'location_type',
    ];

    // 1 location có nhiều placements
    public function placements(): HasMany
    {
        return $this->hasMany(BannerPlacement::class, 'location_id');
    }
}
