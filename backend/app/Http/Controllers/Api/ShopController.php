<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        // Lấy danh sách shop kèm thông tin chủ shop
        $shops = Shop::with('owner:id,email')->get();

        return response()->json($shops);
    }
}