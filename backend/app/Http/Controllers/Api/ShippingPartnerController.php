<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingPartner;

class ShippingPartnerController extends Controller
{
    public function index()
    {
        return response()->json(ShippingPartner::where('status', 'active')->get());
    }
}