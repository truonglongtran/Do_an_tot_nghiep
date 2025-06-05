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

    Route::middleware(['auth:sanctum', 'role:superadmin,admin,moderator'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Admin management (only superadmin)
        Route::middleware('role:superadmin')->group(function () {
            Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
            Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
            Route::get('/admins/{id}', [AdminController::class, 'show'])->name('admins.show');
            Route::put('/admins/{id}', [AdminController::class, 'update'])->name('admins.update');
            Route::delete('/admins/{id}', [AdminController::class, 'destroy'])->name('admins.destroy');
            Route::put('/admins/{id}/status', [AdminController::class, 'updateStatus'])->name('admins.updateStatus');
        });

        // User management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');

        // Shop management
        Route::get('/shops', [ShopController::class, 'index'])->name('shops.index');
        Route::put('/shops/{shop}/status', [ShopController::class, 'updateStatus'])->name('shops.updateStatus');
        Route::delete('/shops/{shop}', [ShopController::class, 'destroy'])->name('shops.destroy');

        // Product management
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
        Route::patch('/products/{id}/status', [ProductController::class, 'updateStatus'])->name('products.updateStatus');
        Route::patch('/variants/{id}/status', [ProductController::class, 'updateVariantStatus'])->name('variants.updateStatus');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Order management
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{id}/settled-status', [OrderController::class, 'updateSettledStatus'])->name('orders.updateSettledStatus');
        Route::put('/orders/{id}/shipping-status', [OrderController::class, 'updateShippingStatus'])->name('orders.updateShippingStatus');
        Route::put('/orders/{id}/order-status', [OrderController::class, 'updateOrderStatus'])->name('orders.updateOrderStatus');
        Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

        // Dispute management
        Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{id}', [DisputeController::class, 'show'])->name('disputes.show');
        Route::put('/disputes/{id}/status', [DisputeController::class, 'updateStatus'])->name('disputes.updateStatus');
        Route::delete('/disputes/{id}', [DisputeController::class, 'destroy'])->name('disputes.destroy');

        // Voucher management
        Route::apiResource('vouchers', VoucherController::class)->names([
            'index' => 'vouchers.index',
            'store' => 'vouchers.store',
            'show' => 'vouchers.show',
            'update' => 'vouchers.update',
            'destroy' => 'vouchers.destroy',
        ]);

        // Shipping partner management
        Route::get('shipping-partners', [ShippingPartnerController::class, 'index'])->name('shipping-partners.index');
        Route::get('shipping-partners/all', [ShippingPartnerController::class, 'all'])->name('shipping-partners.all');
        Route::post('shipping-partners', [ShippingPartnerController::class, 'store'])->name('shipping-partners.store');
        Route::get('shipping-partners/{id}', [ShippingPartnerController::class, 'show'])->name('shipping-partners.show');
        Route::put('shipping-partners/{id}', [ShippingPartnerController::class, 'update'])->name('shipping-partners.update');
        Route::delete('shipping-partners/{id}', [ShippingPartnerController::class, 'destroy'])->name('shipping-partners.destroy');

        // Payment management
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::put('/payments/{id}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::put('/payments/{id}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');

        // Review management
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{shopId}', [ReviewController::class, 'showReviews'])->name('reviews.showReviews');

        // Report management
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Banner management
        Route::apiResource('/banners', BannerController::class)->names([
            'index' => 'banners.index',
            'store' => 'banners.store',
            'show' => 'banners.show',
            'update' => 'banners.update',
            'destroy' => 'banners.destroy',
        ]);
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