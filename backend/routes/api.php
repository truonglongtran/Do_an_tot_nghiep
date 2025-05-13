<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']); // thêm người dùng
        Route::get('/users/{id}', [UserController::class, 'show']); // lấy user cụ thể
        Route::put('/users/{id}', [UserController::class, 'update']); // sửa người dùng
        Route::delete('/users/{id}', [UserController::class, 'destroy']); // xóa người dùng
        Route::put('/users/{id}/status', [UserController::class, 'updateStatus']); // cập nhật trạng thái

        Route::get('/shops', [ShopController::class, 'index']);
        Route::put('/shops/{shop}/status', [ShopController::class, 'updateStatus']);
        Route::delete('/shops/{shop}', [ShopController::class, 'destroy']);


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