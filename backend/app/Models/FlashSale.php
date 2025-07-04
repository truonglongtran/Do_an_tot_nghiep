<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlashSale extends Model
{
    use SoftDeletes; // Sử dụng soft deletes để hỗ trợ xóa mềm

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'product_variant_id',
        'discount_price',
        'stock_limit',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Các cột nên được ép kiểu.
     *
     * @var array
     */
    protected $casts = [
        'discount_price' => 'decimal:2',
        'stock_limit' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Mối quan hệ với model ProductVariant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}