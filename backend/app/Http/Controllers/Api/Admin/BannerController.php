<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Banner;
use App\Models\BannerPlacement;
use App\Models\BannerDisplayLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

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
                $imgUrl = $banner->img_url ? Storage::url($banner->img_url) : '';
                Log::info('Banner image URL', [
                    'banner_id' => $banner->id,
                    'img_url' => $imgUrl,
                    'file_exists' => $banner->img_url ? Storage::exists($banner->img_url) : false,
                ]);
                return [
                    'id' => $banner->id,
                    'title' => $banner->title ?? 'N/A',
                    'img_url' => $imgUrl,
                    'link_url' => $banner->link_url,
                    'position' => $banner->position ?? 'N/A',
                    'start_date' => $banner->start_date ? \Carbon\Carbon::parse($banner->start_date)->toIso8601String() : null,
                    'end_date' => $banner->end_date ? \Carbon\Carbon::parse($banner->end_date)->toIso8601String() : null,
                    'created_at' => $banner->created_at ? \Carbon\Carbon::parse($banner->created_at)->toIso8601String() : null,
                ];
            });

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
            // Log incoming request
            Log::info('Banner creation request received', [
                'has_file' => $request->hasFile('img_url'),
                'request_data' => $request->all(),
                'file_name' => $request->hasFile('img_url') ? $request->file('img_url')->getClientOriginalName() : 'No file',
                'file_size' => $request->hasFile('img_url') ? $request->file('img_url')->getSize() : 'N/A',
                'file_mime' => $request->hasFile('img_url') ? $request->file('img_url')->getMimeType() : 'N/A',
            ]);

            // Validate request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'img_url' => 'required|image|mimes:jpeg,png,gif|max:5120',
                'link_url' => 'nullable|url|max:255',
                'position' => 'required|exists:banner_display_locations,location_name',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Check if file was received
            if (!$request->hasFile('img_url')) {
                Log::error('No image file received in request');
                throw new \Exception('No image file uploaded');
            }

            $file = $request->file('img_url');
            if (!$file->isValid()) {
                Log::error('Uploaded file is invalid', [
                    'file_name' => $file->getClientOriginalName(),
                    'error' => $file->getErrorMessage(),
                ]);
                throw new \Exception('Uploaded file is invalid: ' . $file->getErrorMessage());
            }

            // Ensure banners directory exists
            $directory = 'banners';
            $storagePath = storage_path('app/' . $directory);
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory, 0755, true);
                Log::info('Created banners directory', [
                    'path' => $storagePath,
                    'permissions' => substr(sprintf('%o', fileperms($storagePath)), -4),
                ]);
            }

            // Verify directory is writable
            if (!is_writable($storagePath)) {
                Log::error('Banners directory is not writable', [
                    'path' => $storagePath,
                    'permissions' => substr(sprintf('%o', fileperms($storagePath)), -4),
                ]);
                throw new \Exception('Storage directory is not writable');
            }
            Log::info('Banners directory checked', [
                'path' => $storagePath,
                'writable' => is_writable($storagePath),
                'disk' => config('filesystems.default'),
            ]);

            // Store the image temporarily
            $extension = $file->getClientOriginalExtension();
            $tempFilename = \Illuminate\Support\Str::random(10) . '.' . $extension;
            $tempPath = $file->storeAs($directory, $tempFilename, 'public');

            // Verify temporary file
            if (!$tempPath || !Storage::disk('public')->exists($tempPath)) {
                Log::error('Failed to store temporary image', [
                    'temp_path' => $tempPath,
                    'full_path' => storage_path('app/' . $tempPath),
                    'filename' => $tempFilename,
                ]);
                throw new \Exception('Failed to store temporary image');
            }
            Log::info('Temporary image stored', [
                'temp_path' => $tempPath,
                'full_path' => storage_path('app/' . $tempPath),
                'exists' => Storage::disk('public')->exists($tempPath),
                'size' => Storage::disk('public')->size($tempPath),
            ]);

            // Create the banner
            $banner = Banner::create([
                'title' => $validated['title'],
                'link_url' => $validated['link_url'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'img_url' => $tempPath,
            ]);

            // Rename the image to use banner_id
            $finalFilename = $banner->id . '.' . $extension;
            $finalPath = $directory . '/' . $finalFilename;
            if (!Storage::disk('public')->move($tempPath, $finalPath)) {
                Log::error('Failed to rename image', [
                    'temp_path' => $tempPath,
                    'final_path' => $finalPath,
                    'temp_exists' => Storage::disk('public')->exists($tempPath),
                ]);
                $banner->delete();
                throw new \Exception('Failed to rename image to banner_id');
            }

            // Verify final file
            if (!Storage::disk('public')->exists($finalPath)) {
                Log::error('Final image does not exist after rename', [
                    'final_path' => $finalPath,
                    'full_path' => storage_path('app/' . $finalPath),
                ]);
                $banner->delete();
                throw new \Exception('Final image not found after rename');
            }
            Log::info('Image renamed successfully', [
                'final_path' => $finalPath,
                'full_path' => storage_path('app/' . $finalPath),
                'exists' => Storage::disk('public')->exists($finalPath),
                'size' => Storage::disk('public')->size($finalPath),
            ]);

            // Update banner with final path
            $banner->update(['img_url' => $finalPath]);

            // Create BannerPlacement
            $location = BannerDisplayLocation::where('location_name', $validated['position'])->firstOrFail();
            BannerPlacement::create([
                'banner_id' => $banner->id,
                'location_id' => $location->id,
                'shop_id' => null,
                'display_order' => 1,
                'is_active' => true,
            ]);

            // Verify public URL
            $publicUrl = Storage::disk('public')->url($finalPath);
            $publicPath = public_path($publicUrl);
            Log::info('Banner created successfully', [
                'banner_id' => $banner->id,
                'img_url' => $publicUrl,
                'public_path' => $publicPath,
                'file_exists' => file_exists($publicPath),
                'storage_link' => file_exists(public_path('storage')) ? 'exists' : 'missing',
            ]);

            return response()->json([
                'message' => 'Banner created successfully',
                'banner' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'img_url' => $publicUrl,
                    'link_url' => $banner->link_url,
                    'position' => $validated['position'],
                    'start_date' => $banner->start_date ? \Carbon\Carbon::parse($banner->start_date)->toIso8601String() : null,
                    'end_date' => $banner->end_date ? \Carbon\Carbon::parse($banner->end_date)->toIso8601String() : null,
                    'created_at' => $banner->created_at ? \Carbon\Carbon::parse($banner->created_at)->toIso8601String() : null,
                ],
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Validation error creating banner', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating banner', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Unable to create banner: ' . $e->getMessage()], 500);
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
            $imgUrl = $banner->img_url ? Storage::url($banner->img_url) : '';
            Log::info('Fetching banner', [
                'banner_id' => $id,
                'img_url' => $imgUrl,
                'file_exists' => $banner->img_url ? Storage::exists($banner->img_url) : false,
            ]);

            return response()->json([
                'id' => $banner->id,
                'title' => $banner->title,
                'img_url' => $imgUrl,
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
                'img_url' => 'sometimes|image|mimes:jpeg,png,gif|max:5120',
                'link_url' => 'nullable|url|max:255',
                'position' => 'required|exists:banner_display_locations,location_name',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $updateData = [
                'title' => $validated['title'],
                'link_url' => $validated['link_url'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ];

            if ($request->hasFile('img_url')) {
                $file = $request->file('img_url');
                Log::info('Updating image for banner', [
                    'banner_id' => $id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'is_valid' => $file->isValid(),
                ]);

                if ($banner->img_url && Storage::exists($banner->img_url)) {
                    Storage::delete($banner->img_url);
                    Log::info('Deleted old image', ['path' => $banner->img_url]);
                }

                $directory = 'banners';
                $path = $file->storeAs($directory, $id . '.' . $file->getClientOriginalExtension());
                if (!$path || !Storage::exists($path)) {
                    Log::error('Failed to store updated image', [
                        'path' => $path,
                        'full_path' => storage_path('app/' . $path),
                    ]);
                    throw new \Exception('Failed to store updated image');
                }
                Log::info('Updated image stored', [
                    'path' => $path,
                    'full_path' => storage_path('app/' . $path),
                    'exists' => Storage::exists($path),
                ]);
                $updateData['img_url'] = $path;
            }

            $banner->update($updateData);

            $placement = $banner->placements()->whereNull('shop_id')->first();
            if ($placement) {
                $location = BannerDisplayLocation::where('location_name', $validated['position'])->firstOrFail();
                $placement->update(['location_id' => $location->id]);
            } else {
                $location = BannerDisplayLocation::where('location_name', $validated['position'])->firstOrFail();
                BannerPlacement::create([
                    'banner_id' => $banner->id,
                    'location_id' => $location->id,
                    'shop_id' => null,
                    'display_order' => 1,
                    'is_active' => true,
                ]);
            }

            $publicUrl = $banner->img_url ? Storage::url($banner->img_url) : '';
            Log::info('Banner updated', [
                'banner_id' => $banner->id,
                'img_url' => $publicUrl,
                'file_exists' => $banner->img_url ? Storage::exists($banner->img_url) : false,
            ]);

            return response()->json([
                'message' => 'Banner updated successfully',
                'banner' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'img_url' => $publicUrl,
                    'link_url' => $banner->link_url,
                    'position' => $validated['position'],
                    'start_date' => $banner->start_date ? \Carbon\Carbon::parse($banner->start_date)->toIso8601String() : null,
                    'end_date' => $banner->end_date ? \Carbon\Carbon::parse($banner->end_date)->toIso8601String() : null,
                    'created_at' => $banner->created_at ? \Carbon\Carbon::parse($banner->created_at)->toIso8601String() : null,
                ],
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation error updating banner', [
                'id' => $id,
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
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

            if ($banner->img_url && Storage::exists($banner->img_url)) {
                Storage::delete($banner->img_url);
                Log::info('Deleted banner image', ['path' => $banner->img_url]);
            }

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