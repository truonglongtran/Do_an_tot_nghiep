<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
        'role',
        'status',
    ];

    // Quan hệ với bảng AdminLog
    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class);
    }
}
