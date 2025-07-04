<?php
// app/Http/Controllers/Api/Buyer/AddressController.php
namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\BuyerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $addresses = BuyerAddress::where('user_id', $user->id)
            ->select('id', 'recipient_name', 'phone_number', 'address_line', 'ward', 'district', 'city', 'is_default')
            ->get();

        return response()->json(['addresses' => $addresses]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line' => 'required|string|max:255',
            'ward' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($request, $user) {
            if ($request->is_default) {
                BuyerAddress::where('user_id', $user->id)
                    ->update(['is_default' => false]);
            }

            $address = BuyerAddress::create([
                'user_id' => $user->id,
                'recipient_name' => $request->recipient_name,
                'phone_number' => $request->phone_number,
                'address_line' => $request->address_line,
                'ward' => $request->ward,
                'district' => $request->district,
                'city' => $request->city,
                'is_default' => $request->is_default ?? false,
            ]);

            return $address;
        });

        return response()->json(['message' => 'Address created'], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line' => 'required|string|max:255',
            'ward' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();
        $address = BuyerAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($request, $user, $address) {
            if ($request->is_default) {
                BuyerAddress::where('user_id', $user->id)
                    ->update(['is_default' => false]);
            }

            $address->update([
                'recipient_name' => $request->recipient_name,
                'phone_number' => $request->phone_number,
                'address_line' => $request->address_line,
                'ward' => $request->ward,
                'district' => $request->district,
                'city' => $request->city,
                'is_default' => $request->is_default ?? false,
            ]);
        });

        return response()->json(['message' => 'Address updated']);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $address = BuyerAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($address->is_default) {
            return response()->json(['error' => 'Cannot delete default address'], 400);
        }

        $address->delete();

        return response()->json(['message' => 'Address deleted']);
    }

    public function setDefault(Request $request, $id)
    {
        $user = $request->user();
        $address = BuyerAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($user, $address) {
            BuyerAddress::where('user_id', $user->id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);
        });

        return response()->json(['message' => 'Default address set']);
    }
}