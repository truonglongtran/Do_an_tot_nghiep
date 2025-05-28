<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShippingPartnerController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ReviewController;
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

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::put('/orders/{id}/settled-status', [OrderController::class, 'updateSettledStatus']);
        Route::put('/orders/{id}/shipping-status', [OrderController::class, 'updateShippingStatus']);
        Route::put('/orders/{id}/order-status', [OrderController::class, 'updateOrderStatus']);
        Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

        Route::get('/disputes', [DisputeController::class, 'index']);
        Route::get('/disputes/{id}', [DisputeController::class, 'show']);
        Route::put('/disputes/{id}/status', [DisputeController::class, 'updateStatus']);
        Route::delete('/disputes/{id}', [DisputeController::class, 'destroy']);

        Route::apiResource('vouchers', VoucherController::class);

        Route::get('shipping-partners', [ShippingPartnerController::class, 'index']);
         Route::get('shipping-partners', [ShippingPartnerController::class, 'index']);
        Route::get('shipping-partners/all', [ShippingPartnerController::class, 'all']);
        Route::post('shipping-partners', [ShippingPartnerController::class, 'store']);
        Route::get('shipping-partners/{id}', [ShippingPartnerController::class, 'show']);
        Route::put('shipping-partners/{id}', [ShippingPartnerController::class, 'update']);
        Route::delete('shipping-partners/{id}', [ShippingPartnerController::class, 'destroy']);

        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{id}', [PaymentController::class, 'show']);
        Route::put('/payments/{id}', [PaymentController::class, 'update']);
        Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);
        Route::put('/payments/{id}/status', [PaymentController::class, 'updateStatus']);

        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::get('/reviews/{shopId}', [ReviewController::class, 'showReviews']);

        Route::get('/reports', [ReportController::class, 'index']);

        Route::apiResource('/banners', BannerController::class);
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