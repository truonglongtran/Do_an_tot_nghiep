<?php
namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Category::select('id', 'name')->whereNull('deleted_at');

            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $categories = $query->get();
            return response()->json(['data' => $categories]);
        } catch (\Exception $e) {
            Log::error('Error in SellerCategoryController::index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function attributes($categoryId)
    {
        try {
            $category = Category::with(['attributes.values' => function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            }])->findOrFail($categoryId);
            return response()->json(['data' => $category->attributes]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Category not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in SellerCategoryController::attributes: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}