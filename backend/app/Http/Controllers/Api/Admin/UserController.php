<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Lấy danh sách người dùng
    public function index()
    {
        return response()->json(User::all());
    }

    // Tạo người dùng mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:buyer,seller',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        return response()->json($user, 201);
    }

    // Cập nhật thông tin người dùng
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:buyer,seller',
            'password' => 'nullable|min:6',
        ]);

        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['message' => 'Cập nhật thành công', 'user' => $user]);
    }

    // Cập nhật trạng thái (active/banned)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,banned',
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['message' => 'Trạng thái đã được cập nhật', 'user' => $user]);
    }

    // Xóa người dùng
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Người dùng đã được xóa']);
    }

    // Lấy thông tin người dùng cụ thể (để sửa)
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
}
