<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes; // Nếu bạn sử dụng soft deletes (xóa mềm)

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'image_url',
        'status',
    ];

    /**
     * Các cột nên được ép kiểu.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'status' => 'string',
    ];

    /**
     * Mối quan hệ với model Product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        // return $this->belongsTo(Product::class);
        return $this->belongsTo(Product::class)->with('shop');
    }

    /**
     * Mối quan hệ với model ProductVariantAttribute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class, 'product_variant_id');
    }

    /**
     * Mối quan hệ với model OrderItem.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }
    /**
     * Mối quan hệ với model Cart.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_variant_id');
    }
    /**
     * Mối quan hệ với model FlashSale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flashSales()
    {
        return $this->hasMany(FlashSale::class, 'product_variant_id');
    }
}