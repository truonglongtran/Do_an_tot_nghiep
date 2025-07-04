<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingPartnerController extends Controller
{
    public function index()
    {
        return response()->json(ShippingPartner::where('status', 'active')->get());
    }

    public function all(Request $request)
    {
        try {
            $query = ShippingPartner::with('shops:id,shop_name');

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search by name
            if ($request->has('name') && $request->name) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            $partners = $query->get()->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'api_url' => $partner->api_url,
                    'status' => $partner->status,
                    'shops' => $partner->shops->pluck('shop_name'),
                    'created_at' => $partner->created_at ? $partner->created_at->toIso8601String() : null,
                ];
            });

            return response()->json([
                'partners' => $partners,
                'statuses' => ['active', 'inactive'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching all shipping partners', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to fetch shipping partners'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'api_url' => 'required|url|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            $partner = ShippingPartner::create($validated);

            return response()->json([
                'message' => 'Shipping partner created successfully',
                'partner' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'api_url' => $partner->api_url,
                    'status' => $partner->status,
                    'shops' => [],
                    'created_at' => $partner->created_at ? $partner->created_at->toIso8601String() : null,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating shipping partner', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to create shipping partner'], 500);
        }
    }

    public function show($id)
    {
        try {
            $partner = ShippingPartner::with('shops:id,shop_name')->findOrFail($id);
            return response()->json([
                'id' => $partner->id,
                'name' => $partner->name,
                'api_url' => $partner->api_url,
                'status' => $partner->status,
                'shops' => $partner->shops->pluck('shop_name'),
                'created_at' => $partner->created_at ? $partner->created_at->toIso8601String() : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching shipping partner', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Shipping partner not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $partner = ShippingPartner::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'api_url' => 'sometimes|url|max:255',
                'status' => 'sometimes|in:active,inactive',
            ]);

            $partner->update($validated);

            return response()->json([
                'message' => 'Shipping partner updated successfully',
                'partner' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'api_url' => $partner->api_url,
                    'status' => $partner->status,
                    'shops' => $partner->shops->pluck('shop_name'),
                    'created_at' => $partner->created_at ? $partner->created_at->toIso8601String() : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating shipping partner', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to update shipping partner'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $partner = ShippingPartner::findOrFail($id);
            $validated = $request->validate([
                'status' => 'required|in:active,inactive',
            ]);

            $partner->update(['status' => $validated['status']]);

            return response()->json([
                'message' => 'Shipping partner status updated successfully',
                'partner' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'api_url' => $partner->api_url,
                    'status' => $partner->status,
                    'shops' => $partner->shops->pluck('shop_name'),
                    'created_at' => $partner->created_at ? $partner->created_at->toIso8601String() : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating shipping partner status', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to update shipping partner status'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $partner = ShippingPartner::findOrFail($id);
            $partner->delete();
            return response()->json(['message' => 'Shipping partner deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting shipping partner', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to delete shipping partner'], 500);
        }
    }
}