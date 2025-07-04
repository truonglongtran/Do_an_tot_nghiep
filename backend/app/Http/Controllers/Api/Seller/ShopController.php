<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingPartner;
use App\Models\ShopShippingPartner;
use App\Models\User;
use App\Models\Banner;
use App\Models\BannerDisplayLocation;
use App\Models\BannerPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function profile(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)
                ->with(['owner:id,email'])
                ->firstOrFail();

            $productCount = Product::where('shop_id', $shop->id)
                ->where('status', 'approved')
                ->count();

            $reviews = Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
                ->whereNotNull('rating')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $shop->id,
                    'name' => $shop->shop_name,
                    'avatar_url' => $shop->avatar_url ? Storage::url($shop->avatar_url) : null,
                    'cover_image_url' => $shop->cover_image_url ? Storage::url($shop->cover_image_url) : null,
                    'created_at' => $shop->created_at->toDateTimeString(),
                    'average_rating' => round($reviews->avg('rating'), 2) ?? 0,
                    'product_count' => $productCount,
                    'address' => [
                        'pickup_address' => $shop->pickup_address,
                        'ward' => $shop->ward,
                        'district' => $shop->district,
                        'city' => $shop->city,
                    ],
                    'phone_number' => $shop->phone_number,
                    'owner_email' => $shop->owner->email,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching shop profile', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy hồ sơ shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->firstOrFail();

            $request->validate([
                'shop_name' => 'sometimes|string|max:255',
                'avatar' => 'sometimes|file|image|mimes:jpeg,png,jpg,webp|max:2048',
                'cover_image' => 'sometimes|file|image|mimes:jpeg,png,jpg,webp|max:2048',
                'pickup_address' => 'sometimes|string|max:255',
                'ward' => 'sometimes|string|max:100',
                'district' => 'sometimes|string|max:100',
                'city' => 'sometimes|string|max:100',
                'phone_number' => 'sometimes|string|max:20',
            ]);

            DB::transaction(function () use ($request, $shop) {
                $data = $request->only(['shop_name', 'pickup_address', 'ward', 'district', 'city', 'phone_number']);

                $shopDir = "shops/{$shop->id}";
                if (!Storage::disk('public')->exists($shopDir)) {
                    Storage::disk('public')->makeDirectory($shopDir, 0755, true);
                }

                if ($request->hasFile('avatar')) {
                    if ($shop->avatar_url) {
                        Storage::disk('public')->delete($shop->avatar_url);
                    }
                    $avatarFile = $request->file('avatar');
                    $data['avatar_url'] = $avatarFile->storeAs($shopDir, 'avatar.' . $avatarFile->getClientOriginalExtension(), 'public');
                    Log::info('Avatar stored', [
                        'shop_id' => $shop->id,
                        'path' => $data['avatar_url'],
                    ]);
                }

                if ($request->hasFile('cover_image')) {
                    if ($shop->cover_image_url) {
                        Storage::disk('public')->delete($shop->cover_image_url);
                    }
                    $coverFile = $request->file('cover_image');
                    $data['cover_image_url'] = $coverFile->storeAs($shopDir, 'cover.' . $coverFile->getClientOriginalExtension(), 'public');
                    Log::info('Cover image stored', [
                        'shop_id' => $shop->id,
                        'path' => $data['cover_image_url'],
                    ]);
                }

                $shop->update($data);
            });

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hồ sơ shop thành công',
                'data' => [
                    'avatar_url' => $shop->avatar_url ? Storage::url($shop->avatar_url) : null,
                    'cover_image_url' => $shop->cover_image_url ? Storage::url($shop->cover_image_url) : null,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating shop profile', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật hồ sơ shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function settings(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)
                ->with(['owner:id,email'])
                ->firstOrFail();

            $shippingPartners = ShippingPartner::all()->map(function ($partner) use ($shop) {
                $isActive = ShopShippingPartner::where('shop_id', $shop->id)
                    ->where('shipping_partner_id', $partner->id)
                    ->exists();
                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'is_active' => $isActive,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'contact' => [
                        'email' => $shop->owner->email,
                        'phone_number' => $shop->phone_number,
                    ],
                    'address' => [
                        'pickup_address' => $shop->pickup_address,
                        'ward' => $shop->ward,
                        'district' => $shop->district,
                        'city' => $shop->city,
                    ],
                    'shipping' => [
                        'partners' => $shippingPartners,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching shop settings', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thiết lập shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->firstOrFail();
            $user = $request->user();

            $request->validate([
                'phone_number' => 'sometimes|string|max:20',
                'pickup_address' => 'sometimes|string|max:255',
                'ward' => 'sometimes|string|max:100',
                'district' => 'sometimes|string|max:100',
                'city' => 'sometimes|string|max:100',
                'password' => 'sometimes|string|min:8|confirmed',
                'shipping_partners' => 'sometimes|array',
                'shipping_partners.*' => 'exists:shipping_partners,id',
            ]);

            DB::transaction(function () use ($request, $shop, $user) {
                $shopData = $request->only(['phone_number', 'pickup_address', 'ward', 'district', 'city']);
                $shop->update($shopData);

                if ($request->has('password')) {
                    $user->update([
                        'password' => Hash::make($request->input('password')),
                    ]);
                }

                if ($request->has('shipping_partners')) {
                    ShopShippingPartner::where('shop_id', $shop->id)->delete();
                    foreach ($request->input('shipping_partners', []) as $partnerId) {
                        ShopShippingPartner::create([
                            'shop_id' => $shop->id,
                            'shipping_partner_id' => $partnerId,
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thiết lập shop thành công',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating shop settings', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thiết lập shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function decoration(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->firstOrFail();

            $banners = Banner::whereHas('placements', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
                ->with(['placements' => fn($q) => $q->with('location')])
                ->get()
                ->map(function ($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'img_url' => $banner->img_url ? Storage::url($banner->img_url) : null,
                        'link_url' => $banner->link_url,
                        'placements' => $banner->placements->map(function ($placement) {
                            return [
                                'location_id' => $placement->location_id,
                                'location' => [
                                    'location_name' => $placement->location->location_name,
                                ],
                                'display_order' => $placement->display_order,
                                'is_active' => $placement->is_active,
                            ];
                        }),
                    ];
                });

            $locations = BannerDisplayLocation::where('location_type', 'shop')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'banners' => $banners,
                    'locations' => $locations,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching shop decoration', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu trang trí shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeBanner(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->firstOrFail();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpg,png,jpeg,webp|max:2048',
                'link' => 'nullable|url',
                'status' => 'required|in:active,inactive',
                'location_id' => [
                    'required',
                    'exists:banner_display_locations,id',
                    function ($attribute, $value, $fail) {
                        $location = BannerDisplayLocation::find($value);
                        if ($location && $location->location_type !== 'shop') {
                            $fail('Vị trí này chỉ dành cho banner chung của nền tảng.');
                        }
                    },
                ],
                'position' => 'required|integer|min:1',
            ]);

            return DB::transaction(function () use ($request, $validated, $shop, $sellerId) {
                $bannerDir = "shops/{$shop->id}/banners";
                if (!Storage::disk('public')->exists($bannerDir)) {
                    Storage::disk('public')->makeDirectory($bannerDir, 0755, true);
                }

                // Store the image first
                $imageFile = $request->file('image');
                if (!$imageFile->isValid()) {
                    throw new \Exception('Tệp hình ảnh không hợp lệ');
                }

                // Create a temporary banner ID for naming
                $tempBannerId = uniqid();
                $imgUrl = $imageFile->storeAs($bannerDir, "{$tempBannerId}." . $imageFile->getClientOriginalExtension(), 'public');

                // Create the banner with img_url
                $banner = Banner::create([
                    'title' => $validated['title'],
                    'link_url' => $validated['link'],
                    'img_url' => $imgUrl,
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                ]);

                // Rename the image with the actual banner ID
                $finalImgUrl = "shops/{$shop->id}/banners/{$banner->id}." . $imageFile->getClientOriginalExtension();
                Storage::disk('public')->move($imgUrl, $finalImgUrl);
                $banner->update(['img_url' => $finalImgUrl]);

                BannerPlacement::create([
                    'banner_id' => $banner->id,
                    'location_id' => $validated['location_id'],
                    'shop_id' => $shop->id,
                    'display_order' => $validated['position'],
                    'is_active' => $validated['status'] === 'active',
                ]);

                Log::info('Banner created', [
                    'seller_id' => $sellerId,
                    'shop_id' => $shop->id,
                    'banner_id' => $banner->id,
                    'image_path' => $banner->img_url,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'img_url' => Storage::url($banner->img_url),
                        'link_url' => $banner->link_url,
                        'placements' => [
                            [
                                'location_id' => $validated['location_id'],
                                'location' => [
                                    'location_name' => BannerDisplayLocation::find($validated['location_id'])->location_name,
                                ],
                                'display_order' => $validated['position'],
                                'is_active' => $validated['status'] === 'active',
                            ],
                        ],
                    ],
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating banner', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo banner: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateBanner(Request $request, $id)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->firstOrFail();
            $banner = Banner::whereHas('placements', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })->findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:2048',
                'link' => 'nullable|url',
                'status' => 'required|in:active,inactive',
                'location_id' => [
                    'required',
                    'exists:banner_display_locations,id',
                    function ($attribute, $value, $fail) {
                        $location = BannerDisplayLocation::find($value);
                        if ($location && $location->location_type !== 'shop') {
                            $fail('Vị trí này chỉ dành cho banner chung của nền tảng.');
                        }
                    },
                ],
                'position' => 'required|integer|min:1',
            ]);

            return DB::transaction(function () use ($request, $validated, $shop, $banner) {
                $bannerDir = "shops/{$shop->id}/banners";
                if (!Storage::disk('public')->exists($bannerDir)) {
                    Storage::disk('public')->makeDirectory($bannerDir, 0755, true);
                }

                if ($request->hasFile('image')) {
                    if ($banner->img_url) {
                        Storage::disk('public')->delete($banner->img_url);
                    }
                    $imageFile = $request->file('image');
                    $validated['img_url'] = $imageFile->storeAs($bannerDir, "{$banner->id}." . $imageFile->getClientOriginalExtension(), 'public');
                }

                $banner->update([
                    'title' => $validated['title'],
                    'link_url' => $validated['link'],
                    'img_url' => $validated['img_url'] ?? $banner->img_url,
                ]);

                $banner->placements()->updateOrCreate(
                    ['banner_id' => $banner->id, 'shop_id' => $shop->id],
                    [
                        'location_id' => $validated['location_id'],
                        'shop_id' => $shop->id,
                        'display_order' => $validated['position'],
                        'is_active' => $validated['status'] === 'active',
                    ]
                );

                Log::info('Banner updated', [
                    'seller_id' => $request->user()->id,
                    'shop_id' => $shop->id,
                    'banner_id' => $banner->id,
                    'image_path' => $banner->img_url,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'img_url' => Storage::url($banner->img_url),
                        'link_url' => $banner->link_url,
                        'placements' => [
                            [
                                'location_id' => $validated['location_id'],
                                'location' => [
                                    'location_name' => BannerDisplayLocation::find($validated['location_id'])->location_name,
                                ],
                                'display_order' => $validated['position'],
                                'is_active' => $validated['status'] === 'active',
                            ],
                        ],
                    ],
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating banner', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật banner: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteBanner(Request $request, $id)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->firstOrFail();
            $banner = Banner::whereHas('placements', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })->findOrFail($id);

            return DB::transaction(function () use ($banner, $shop, $sellerId, $id) {
                if ($banner->img_url) {
                    Storage::disk('public')->delete($banner->img_url);
                    Log::info('Banner image deleted', [
                        'shop_id' => $shop->id,
                        'banner_id' => $id,
                        'image_path' => $banner->img_url,
                    ]);
                }

                $banner->placements()->where('shop_id', $shop->id)->delete();
                $banner->delete();

                Log::info('Banner deleted', [
                    'seller_id' => $sellerId,
                    'shop_id' => $shop->id,
                    'banner_id' => $id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Xóa banner thành công',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error deleting banner', [
                'seller_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa banner: ' . $e->getMessage(),
            ], 500);
        }
    }
}