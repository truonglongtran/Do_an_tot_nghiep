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
                ->first();

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop',
                ], 404);
            }

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
                'message' => 'Lỗi khi lấy hồ sơ shop',
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop',
                ], 404);
            }

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

                if ($request->hasFile('avatar')) {
                    if ($shop->avatar_url) {
                        Storage::disk('public')->delete($shop->avatar_url);
                    }
                    $data['avatar_url'] = $request->file('avatar')->store('shop_avatars', 'public');
                }

                if ($request->hasFile('cover_image')) {
                    if ($shop->cover_image_url) {
                        Storage::disk('public')->delete($shop->cover_image_url);
                    }
                    $data['cover_image_url'] = $request->file('cover_image')->store('shop_covers', 'public');
                }

                $shop->update($data);
            });

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hồ sơ shop thành công',
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
                'message' => 'Lỗi khi cập nhật hồ sơ shop',
            ], 500);
        }
    }

    public function settings(Request $request)
{
    try {
        $sellerId = $request->user()->id;
        $shop = Shop::where('owner_id', $sellerId)
            ->with(['owner:id,email'])
            ->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy shop',
            ], 404);
        }

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
            'message' => 'Lỗi khi lấy thiết lập shop',
        ], 500);
    }
}


    public function updateSettings(Request $request)
    {
        try {
            $sellerId = $request->user()->id;
            $shop = Shop::where('owner_id', $sellerId)->first();

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy shop',
                ], 404);
            }

            $request->validate([
                'phone_number' => 'sometimes|string|max:20',
                'pickup_address' => 'sometimes|string|max:255',
                'ward' => 'sometimes|string|max:100',
                'district' => 'sometimes|string|max:100',
                'city' => 'sometimes|string|max:100',
                'shipping_partners' => 'sometimes|array',
                'shipping_partners.*' => 'exists:shipping_partners,id',
                'password' => 'sometimes|nullable|string|min:8|confirmed',
            ]);

            DB::transaction(function () use ($request, $shop, $sellerId) {
                $shopData = $request->only([
                    'phone_number',
                    'pickup_address',
                    'ward',
                    'district',
                    'city',
                ]);

                $shop->update($shopData);

                if ($request->has('shipping_partners')) {
                    ShopShippingPartner::where('shop_id', $shop->id)->delete();
                    foreach ($request->shipping_partners as $partnerId) {
                        ShopShippingPartner::create([
                            'shop_id' => $shop->id,
                            'shipping_partner_id' => $partnerId,
                        ]);
                    }
                }

                if ($request->filled('password')) {
                    User::where('id', $sellerId)->update([
                        'password' => Hash::make($request->password),
                    ]);
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
                'message' => 'Lỗi khi cập nhật thiết lập shop',
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
                ->with(['placements.location'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'banners' => $banners,
                    'locations' => BannerDisplayLocation::where('location_type', 'shop')->get(),
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
                'image' => 'required|image|mimes:jpg,png,jpeg|max:2048',
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

            $imagePath = $request->file('image')->store('banners', 'public');
            $banner = Banner::create([
                'title' => $validated['title'],
                'img_url' => Storage::url($imagePath),
                'link_url' => $validated['link'],
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ]);

            BannerPlacement::create([
                'banner_id' => $banner->id,
                'location_id' => $validated['location_id'],
                'shop_id' => $shop->id,
                'display_order' => $validated['position'],
                'is_active' => $validated['status'] === 'active',
            ]);

            Log::info('Banner created', [
                'seller_id' => $sellerId,
                'banner_id' => $banner->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $banner,
            ]);
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
                'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
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

            if ($request->hasFile('image')) {
                if ($banner->img_url) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $banner->img_url));
                }
                $imagePath = $request->file('image')->store('banners', 'public');
                $validated['img_url'] = Storage::url($imagePath);
            }

            $banner->update($validated);
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
                'seller_id' => $sellerId,
                'banner_id' => $banner->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $banner,
            ]);
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

            if ($banner->img_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->img_url));
            }
            $banner->placements()->where('shop_id', $shop->id)->delete();
            $banner->delete();

            Log::info('Banner deleted', [
                'seller_id' => $sellerId,
                'banner_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Xóa banner thành công',
            ]);
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