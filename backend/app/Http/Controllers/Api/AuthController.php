<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $type = $request->segment(2); // 'admin', 'seller', hoặc 'buyer'

        if ($type === 'admin') {
            // Xác thực với bảng admins
            $admin = Admin::where('email', $credentials['email'])->first();
            if ($admin && Hash::check($credentials['password'], $admin->password)) {
                if ($admin->status !== 'active') {
                    return response()->json(['message' => 'Tài khoản bị khóa admin'], 403);
                }
                $token = $admin->createToken('admin-token', ['role:admin'])->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => $admin,
                    'role' => 'admin',
                ], 200);
            }
        } else {
            // Xác thực với bảng users (cho buyer/seller)
            $user = User::where('email', $credentials['email'])->first();
            if ($user && Hash::check($credentials['password'], $user->password)) {
                if ($user->status !== 'active') {
                    return response()->json(['message' => 'Tài khoản bị khóa'], 403);
                }

                // Kiểm tra vai trò chỉ cho /seller/login
                if ($type === 'seller' && $user->role !== 'seller') {
                    return response()->json(['message' => 'Chỉ người bán mới có thể đăng nhập tại đây'], 403);
                }

                // Không kiểm tra vai trò cho /buyer/login, cho phép cả buyer và seller
                $token = $user->createToken('user-token', ['role:' . $user->role])->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'role' => $user->role,
                ], 200);
            }
        }

        return response()->json(['message' => 'Thông tin đăng nhập không hợp lệ'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đã đăng xuất'], 200);
    }
}