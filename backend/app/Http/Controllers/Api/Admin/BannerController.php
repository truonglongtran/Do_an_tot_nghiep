<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerPlacement;
use App\Models\BannerDisplayLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Banner::query()
                ->leftJoin('banner_placements', 'banners.id', '=', 'banner_placements.banner_id')
                ->leftJoin('banner_display_locations', 'banner_placements.location_id', '=', 'banner_display_locations.id')
                ->whereNull('banner_placements.shop_id')
                ->select('banners.*', 'banner_display_locations.location_name as position');

            if ($request->has('position') && $request->position !== 'all') {
                $query->where('banner_display_locations.location_name', $request->position);
            }

            if ($request->has('title') && $request->title) {
                $query->where('banners.title', 'like', '%' . $request->title . '%');
            }

            $banners = $query->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'N/A',
                    'img_url' => $banner->img_url ?? '',
                    'link_url' => $banner->link_url,
                    'position' => $banner->position ?? 'N/A',
                    'start_date' => $banner->start_date ? \Carbon\Carbon::parse($banner->start_date)->toIso8601String() : null,
                    'end_date' => $banner->end_date ? \Carbon\Carbon::parse($banner->end_date)->toIso8601String() : null,
                    'created_at' => $banner->created_at ? \Carbon\Carbon::parse($banner->created_at)->toIso8601String() : null,
                ];
            });

            // Fetch unique position values only for admin banners (shop_id = null)
            $positions = BannerDisplayLocation::whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('banner_placements')
                    ->whereColumn('banner_placements.location_id', 'banner_display_locations.id')
                    ->whereNull('banner_placements.shop_id');
            })->pluck('location_name')->unique()->values()->toArray();

            return response()->json([
                'banners' => $banners,
                'positions' => $positions,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching banners', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to fetch banners'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'img_url' => 'required|url|max:255',
                'link_url' => 'nullable|url|max:255',
                'position' => 'required|exists:banner_display_locations,location_name',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $banner = Banner::create([
                'title' => $validated['title'],
                'img_url' => $validated['img_url'],
                'link_url' => $validated['link_url'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            // Find the location_id for the given position
            $location = BannerDisplayLocation::where('location_name', $validated['position'])->firstOrFail();

            // Create a BannerPlacement with shop_id = null
            BannerPlacement::create([
                'banner_id' => $banner->id,
                'location_id' => $location->id,
                'shop_id' => null,
                'display_order' => 1,
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'Banner created successfully',
                'banner' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'img_url' => $banner->img_url,
                    'link_url' => $banner->link_url,
                    'position' => $validated['position'],
                    'start_date' => $banner->start_date ? \Carbon\Carbon::parse($banner->start_date)->toIso8601String() : null,
                    'end_date' => $banner->end_date ? \Carbon\Carbon::parse($banner->end_date)->toIso8601String() : null,
                    'created_at' => $banner->created_at ? \Carbon\Carbon::parse($banner->created_at)->toIso8601String() : null,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating banner', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to create banner'], 500);
        }
    }

    public function show($id)
    {
        try {
            $banner = Banner::whereHas('placements', function ($query) {
                $query->whereNull('shop_id');
            })
            ->with(['placements.location' => function ($query) {
                $query->select('id', 'location_name');
            }])
            ->findOrFail($id);

            $position = $banner->placements->first()->location->location_name ?? 'N/A';

            return response()->json([
                'id' => $banner->id,
                'title' => $banner->title,
                'img_url' => $banner->img_url,
                'link_url' => $banner->link_url,
                'position' => $position,
                'start_date' => $banner->start_date ? \Carbon\Carbon::parse($banner->start_date)->toIso8601String() : null,
                'end_date' => $banner->end_date ? \Carbon\Carbon::parse($banner->end_date)->toIso8601String() : null,
                'created_at' => $banner->created_at ? \Carbon\Carbon::parse($banner->created_at)->toIso8601String() : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching banner', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Banner not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::whereHas('placements', function ($query) {
                $query->whereNull('shop_id');
            })->findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'img_url' => 'required|url|max:255',
                'link_url' => 'nullable|url|max:255',
                'position' => 'required|exists:banner_display_locations,location_name',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $banner->update([
                'title' => $validated['title'],
                'img_url' => $validated['img_url'],
                'link_url' => $validated['link_url'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            // Update the associated BannerPlacement
            $placement = $banner->placements()->whereNull('shop_id')->first();
            if ($placement) {
                $location = BannerDisplayLocation::where('location_name', $validated['position'])->firstOrFail();
                $placement->update([
                    'location_id' => $location->id,
                ]);
            } else {
                // Create a new placement if none exists
                $location = BannerDisplayLocation::where('location_name', $validated['position'])->firstOrFail();
                BannerPlacement::create([
                    'banner_id' => $banner->id,
                    'location_id' => $location->id,
                    'shop_id' => null,
                    'display_order' => 1,
                    'is_active' => true,
                ]);
            }

            return response()->json([
                'message' => 'Banner updated successfully',
                'banner' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'img_url' => $banner->img_url,
                    'link_url' => $banner->link_url,
                    'position' => $validated['position'],
                    'start_date' => $banner->start_date ? \Carbon\Carbon::parse($banner->start_date)->toIso8601String() : null,
                    'end_date' => $banner->end_date ? \Carbon\Carbon::parse($banner->end_date)->toIso8601String() : null,
                    'created_at' => $banner->created_at ? \Carbon\Carbon::parse($banner->created_at)->toIso8601String() : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating banner', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to update banner'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::whereHas('placements', function ($query) {
                $query->whereNull('shop_id');
            })->findOrFail($id);

            // Delete associated placements with shop_id = null
            $banner->placements()->whereNull('shop_id')->delete();
            $banner->delete();

            return response()->json(['message' => 'Banner deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting banner', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to delete banner'], 500);
        }
    }
}