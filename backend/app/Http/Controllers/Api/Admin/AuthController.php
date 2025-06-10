<?php
namespace App\Http\Controllers\Api\Amin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

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
                    'user' => $admin,
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
                    'user' => $user,
                    'role' => $user->role,
                    'loginType' => $type,
                ], 200);
            }
        }

        RateLimiter::hit('login:' . $email);
        return response()->json(['message' => 'Thông tin đăng nhập không hợp lệ'], 401);
    }

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