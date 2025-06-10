<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class AdminController extends Controller
{
    public function index()
    {
        try {
            $admins = Admin::where('role', '!=', 'superadmin')->get();
            return response()->json($admins);
        } catch (\Exception $e) {
            \Log::error('Lỗi trong AdminController::index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi server'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|min:6',
                'role' => 'required|in:admin,moderator',
            ]);

            $admin = Admin::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status' => 'active',
            ]);

            return response()->json($admin, 201);
        } catch (\Exception $e) {
            \Log::error('Lỗi trong AdminController::store: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi khi tạo admin'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $admin = Admin::findOrFail($id);

            $validated = $request->validate([
                'email' => 'required|email|unique:admins,email,' . $id,
                'role' => 'required|in:admin,moderator',
                'password' => 'nullable|min:6',
            ]);

            $admin->email = $validated['email'];
            $admin->role = $validated['role'];

            if (!empty($validated['password'])) {
                $admin->password = Hash::make($validated['password']);
            }

            $admin->save();

            return response()->json(['message' => 'Cập nhật thành công', 'admin' => $admin]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Admin không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi trong AdminController::update: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi khi cập nhật admin'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            \Log::info('Starting updateStatus', ['id' => $id, 'request' => $request->all()]);
            
            $validated = $request->validate([
                'status' => 'required|in:active,inactive',
            ]);

            \Log::info('Validation passed', ['validated' => $validated]);

            $admin = Admin::findOrFail($id);
            \Log::info('Admin found', ['admin' => $admin->toArray()]);

            $admin->status = $validated['status'];
            $admin->save();

            \Log::info('Status updated', ['admin' => $admin->toArray()]);

            return response()->json(['message' => 'Trạng thái đã được cập nhật', 'admin' => $admin]);
        } catch (ModelNotFoundException $e) {
            \Log::error('Admin not found', ['id' => $id, 'exception' => $e->getMessage()]);
            return response()->json(['error' => 'Admin không tồn tại'], 404);
        } catch (QueryException $e) {
            \Log::error('Database query error in AdminController::updateStatus', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'sql' => $e->getSql()
            ]);
            return response()->json(['error' => 'Lỗi cơ sở dữ liệu'], 500);
        } catch (\Exception $e) {
            \Log::error('Unexpected error in AdminController::updateStatus', [
                'id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            $admin->delete();

            return response()->json(['message' => 'Admin đã được xóa']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Admin không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi trong AdminController::destroy: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi khi xóa admin'], 500);
        }
    }

    public function show($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            return response()->json($admin);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Admin không tồn tại'], 404);
        } catch (\Exception $e) {
            \Log::error('Lỗi trong AdminController::show: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Lỗi server'], 500);
        }
    }
}
?>