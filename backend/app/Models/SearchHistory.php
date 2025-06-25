<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SearchHistory extends Model
{
    use SoftDeletes;

    /**
     * Tên bảng liên kết với model.
     *
     * @var string
     */
    protected $table = 'search_histories';

    /**
     * Các cột có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'keyword',
    ];

    /**
     * Các cột nên được ép kiểu.
     *
     * @var array
     */
    protected $casts = [
        'keyword' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Mối quan hệ với model User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}