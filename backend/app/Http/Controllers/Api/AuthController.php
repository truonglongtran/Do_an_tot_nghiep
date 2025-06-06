<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $credentials['email'];
        $type = $request->segment(2); // 'admin', 'seller', hoặc 'buyer'

        // Kiểm tra nếu người dùng đã bị khóa
        if (Cache::has('login_locked_' . $email)) {
            return response()->json(['message' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau 30 phút.'], 403);
        }

        // Kiểm tra số lần thử đăng nhập với Rate Limiting
        if (RateLimiter::tooManyAttempts('login:' . $email, 2)) {
            // Khóa tài khoản trong 30 phút (1800 giây)
            Cache::put('login_locked_' . $email, true, 1800);
            return response()->json(['message' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau 30 phút.'], 403);
        }

        if ($type === 'admin') {
            $admin = Admin::where('email', $credentials['email'])->first();
            if ($admin && Hash::check($credentials['password'], $admin->password)) {
                if ($admin->status !== 'active') {
                    return response()->json(['message' => 'Tài khoản bị khóa admin'], 403);
                }
                RateLimiter::clear('login:' . $email);
                $token = $admin->createToken('admin-token', ['role:' . $admin->role])->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => $admin,
                    'role' => $admin->role,
                ], 200);
            }
        } else {
            $user = User::where('email', $credentials['email'])->first();
            if ($user && Hash::check($credentials['password'], $user->password)) {
                if ($user->status !== 'active') {
                    return response()->json(['message' => 'Tài khoản bị khóa'], 403);
                }
                RateLimiter::clear('login:' . $email); // Xóa giới hạn khi đăng nhập thành công
                $token = $user->createToken('user-token', ['role:' . $user->role])->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'role' => $user->role,
                ], 200);
            }
        }

        RateLimiter::hit('login:' . $email);

        return response()->json(['message' => 'Thông tin đăng nhập không hợp lệ'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đã đăng xuất'], 200);
    }
}