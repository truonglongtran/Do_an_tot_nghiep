<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $table = 'attribute_values';
    protected $fillable = ['attribute_id', 'category_id', 'value'];
    protected $hidden = ['created_at', 'updated_at'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}