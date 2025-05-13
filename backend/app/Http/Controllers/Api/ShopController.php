<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
  public function index()
    {
        $shops = Shop::with([
            'owner:id,email',
            'shippingPartners:id,name',
        ])->get();

        return response()->json($shops);
    }
   public function updateStatus(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,active,banned',
        ]);

        $shop->status = $validated['status'];
        $shop->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }


    public function destroy(Shop $shop)
    {
        $shop->delete();

        return response()->json(['message' => 'Xóa cửa hàng thành công']);
    }
}