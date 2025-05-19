<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlatformVoucher extends Model
{
    protected $fillable = ['voucher_id'];
    public function voucher() { return $this->belongsTo(Voucher::class); }
}