<?php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\ShippingPartner;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'owner_id' => 'required|exists:users,id',
            ], [
                'owner_id.required' => 'The owner id field is required.',
                'owner_id.exists' => 'The selected owner id is invalid.',
            ]);

            $ownerId = $request->query('owner_id');

            // Find the shop associated with the owner_id
            $shop = Shop::where('owner_id', $ownerId)->first();

            if (!$shop) {
                return response()->json(['error' => 'No shop found for the provided owner ID.'], 404);
            }

            // Retrieve shipping methods for the shop
            $methods = ShippingPartner::whereHas('shops', function ($query) use ($shop) {
                $query->where('shops.id', $shop->id);
            })->get(['id', 'name'])->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'price' => 15000, // Default price since cost is not in shop_shipping_partners
                    'description' => 'Standard shipping method', // Static description or fetch from another source
                ];
            });

            return response()->json(['methods' => $methods], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Lỗi ShippingMethodController@index: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching shipping methods.'], 500);
        }
    }
}