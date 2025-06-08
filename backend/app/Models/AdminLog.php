<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'admin_email',
        'action_type',
        'target_type',
        'target_id',
        'route_name',
        'method',
        'description',
        'created_at',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public static function logAction($admin, $actionType, $targetType = null, $targetId = null, $description = null)
    {
        return self::create([
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'route_name' => request()->route()->getName(),
            'method' => request()->method(),
            'description' => $description,
        ]);
    }
}