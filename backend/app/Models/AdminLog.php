<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action_type',
        'target_id',
        'description',
    ];

    // Quan hệ với bảng Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
