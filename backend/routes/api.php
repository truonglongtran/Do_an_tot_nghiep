<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/admins', [AdminController::class, 'index']); // Lấy danh sách admin
        Route::post('/admins', [AdminController::class, 'store']); // Thêm admin
        Route::get('/admins/{id}', [AdminController::class, 'show']); // Lấy admin cụ thể
        Route::put('/admins/{id}', [AdminController::class, 'update']); // Sửa admin
        Route::delete('/admins/{id}', [AdminController::class, 'destroy']); // Xóa admin
        Route::put('/admins/{id}/status', [AdminController::class, 'updateStatus']); // Cập nhật trạng thái admin

        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']); // Thêm người dùng
        Route::get('/users/{id}', [UserController::class, 'show']); // Lấy user cụ thể
        Route::put('/users/{id}', [UserController::class, 'update']); // Sửa người dùng
        Route::delete('/users/{id}', [UserController::class, 'destroy']); // Xóa người dùng
        Route::put('/users/{id}/status', [UserController::class, 'updateStatus']); // Cập nhật trạng thái

        Route::get('/shops', [ShopController::class, 'index']);
        Route::put('/shops/{shop}/status', [ShopController::class, 'updateStatus']);
        Route::delete('/shops/{shop}', [ShopController::class, 'destroy']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::patch('/products/{id}/status', [ProductController::class, 'updateStatus']);
        Route::patch('/variants/{id}/status', [ProductController::class, 'updateVariantStatus']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });
});

Route::prefix('seller')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::prefix('buyer')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
?>