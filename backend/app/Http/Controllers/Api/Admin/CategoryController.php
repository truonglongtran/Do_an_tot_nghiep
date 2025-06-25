<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Áp dụng middleware auth:sanctum và kiểm tra vai trò admin
        $this->middleware('auth:sanctum');
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !in_array($user->role, ['superadmin', 'admin', 'moderator'])) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        try {
            $query = Category::select('id', 'name')->whereNull('deleted_at');

            if ($search = $request->input('search')) {
                $query->where('name', 'like', "%{$search}%");
            }

            $categories = $query->get();
            return response()->json([
                'success' => true,
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in AdminCategoryController::index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'error' => 'Lỗi máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function attributes($categoryId)
    {
        try {
            $category = Category::with(['attributes.values' => function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            }])->findOrFail($categoryId);
            return response()->json([
                'success' => true,
                'data' => $category->attributes
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Danh mục không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in AdminCategoryController::attributes: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'error' => 'Lỗi máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }
}