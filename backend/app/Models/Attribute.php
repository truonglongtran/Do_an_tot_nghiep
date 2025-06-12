<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $table = 'attributes';
    protected $fillable = ['name', 'type'];
    protected $hidden = ['created_at', 'updated_at'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attribute');
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}