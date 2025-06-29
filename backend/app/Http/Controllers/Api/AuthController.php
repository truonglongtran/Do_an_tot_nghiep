<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Login for admin, seller, or buyer.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $credentials['email'];
        $type = $request->segment(2); // 'admin', 'seller', or 'buyer'

        if (Cache::has('login_locked_' . $email)) {
            return response()->json(['message' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau 30 phút.'], 403);
        }

        if (RateLimiter::tooManyAttempts('login:' . $email, 2)) {
            Cache::put('login_locked_' . $email, true, 1800);
            return response()->json(['message' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau 30 phút.'], 403);
        }

        if ($type === 'admin') {
            $admin = Admin::where('email', $credentials['email'])->first();
            if ($admin && Hash::check($credentials['password'], $admin->password)) {
                if ($admin->status !== 'active') {
                    return response()->json(['message' => 'Tài khoản bị khóa'], 403);
                }
                RateLimiter::clear('login:' . $email);
                $token = $admin->createToken('admin-token', ['role:' . $admin->role])->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => [
                        'id' => $admin->id,
                        'email' => $admin->email,
                        'username' => $admin->username ?? 'Admin',
                        'avatar_url' => $admin->avatar_url ?? 'https://via.placeholder.com/50',
                        'role' => $admin->role,
                    ],
                    'role' => $admin->role,
                    'loginType' => 'admin',
                ], 200);
            }
        } else {
            $user = User::where('email', $credentials['email'])->first();
            if ($user && Hash::check($credentials['password'], $user->password)) {
                if ($user->status !== 'active') {
                    return response()->json(['message' => 'Tài khoản bị khóa'], 403);
                }
                RateLimiter::clear('login:' . $email);
                $token = $user->createToken('user-token', ['role:' . $user->role])->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'username' => $user->username ?? 'Người dùng',
                        'avatar_url' => $user->avatar_url ?? 'https://via.placeholder.com/50',
                        'role' => $user->role,
                    ],
                    'role' => $user->role,
                    'loginType' => $type,
                ], 200);
            }
        }

        RateLimiter::hit('login:' . $email);
        return response()->json(['message' => 'Thông tin đăng nhập không hợp lệ'], 401);
    }

    /**
     * Register a new buyer.
     */
    public function registerBuyer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'username' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            Log::warning('Buyer registration validation failed', [
                'errors' => $validator->errors()->all(),
                'request' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $user = new User();
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->username = $request->username;
            $user->phone_number = $request->phone_number;
            $user->role = 'buyer';
            $user->status = 'active';
            $user->save();

            $token = $user->createToken('user-token', ['role:buyer'])->plainTextToken;

            DB::commit();

            Log::info('Buyer registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký người mua thành công',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'avatar_url' => $user->avatar_url ?? 'https://via.placeholder.com/50',
                    'role' => $user->role,
                ],
                'role' => 'buyer',
                'loginType' => 'buyer',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in buyer registration: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Lỗi khi đăng ký người mua'], 500);
        }
    }

    /**
     * Register a new seller or convert a buyer to a seller.
     */
    public function registerSeller(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'nullable|required_if:is_new_user,true|min:6',
            'username' => 'required_if:is_new_user,true|string|max:255',
            'phone_number' => 'required_if:is_new_user,true|string|max:20',
            'shop_name' => 'required|string|max:255',
            'pickup_address' => 'required|string|max:255',
            'ward' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'shop_phone_number' => 'required|string|max:20',
            'is_new_user' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            Log::warning('Seller registration validation failed', [
                'errors' => $validator->errors()->all(),
                'request' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->first();
            $isNewUser = $request->is_new_user;

            if ($isNewUser) {
                if ($user) {
                    return response()->json(['success' => false, 'message' => 'Email đã tồn tại'], 422);
                }

                $user = new User();
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->username = $request->username;
                $user->phone_number = $request->phone_number;
                $user->role = 'seller';
                $user->status = 'active';
                $user->save();
            } else {
                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'Tài khoản không tồn tại'], 404);
                }
                if ($user->role === 'seller') {
                    return response()->json(['success' => false, 'message' => 'Tài khoản đã là người bán'], 403);
                }
                if ($user->status !== 'active') {
                    return response()->json(['success' => false, 'message' => 'Tài khoản bị khóa'], 403);
                }
                $user->role = 'seller';
                $user->save();
            }

            // Create shop
            $shop = new Shop();
            $shop->owner_id = $user->id;
            $shop->shop_name = $request->shop_name;
            $shop->pickup_address = $request->pickup_address;
            $shop->ward = $request->ward;
            $shop->district = $request->district;
            $shop->city = $request->city;
            $shop->phone_number = $request->shop_phone_number;
            $shop->status = 'pending';
            $shop->save();

            $token = $user->createToken('user-token', ['role:seller'])->plainTextToken;

            DB::commit();

            Log::info('Seller registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'shop_id' => $shop->id,
                'is_new_user' => $isNewUser,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký người bán thành công. Cửa hàng đang chờ duyệt.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username ?? 'Người dùng',
                    'avatar_url' => $user->avatar_url ?? 'https://via.placeholder.com/50',
                    'role' => $user->role,
                ],
                'role' => 'seller',
                'loginType' => 'seller',
                'shop' => [
                    'id' => $shop->id,
                    'shop_name' => $shop->shop_name,
                    'status' => $shop->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in seller registration: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Lỗi khi đăng ký người bán'], 500);
        }
    }

    /**
     * Logout for authenticated users.
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                Log::warning('Không tìm thấy người dùng khi đăng xuất', ['token' => $request->bearerToken()]);
                return response()->json(['message' => 'Token không hợp lệ hoặc đã đăng xuất'], 401);
            }

            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
                return response()->json(['message' => 'Đăng xuất thành công'], 200);
            }

            Log::warning('Không tìm thấy token hiện tại khi đăng xuất', ['user_id' => $user->id]);
            return response()->json(['message' => 'Token không hợp lệ hoặc đã đăng xuất'], 401);
        } catch (\Exception $e) {
            Log::error('Lỗi khi đăng xuất: ' . $e->getMessage(), [
                'user_id' => $user ? $user->id : null,
                'token' => $request->bearerToken(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Lỗi server khi đăng xuất'], 500);
        }
    }
}