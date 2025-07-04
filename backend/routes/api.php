<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\DisputeController;
use App\Http\Controllers\Api\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Api\Admin\PaymentController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\ShippingPartnerController as AdminShippingPartnerController;
use App\Http\Controllers\Api\Admin\BannerController as AdminBannerController; 
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Api\Seller\ShippingPartnerController as SellerShippingPartnerController;
use App\Http\Controllers\Api\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Api\Seller\CategoryController as SellerCategoryController;
use App\Http\Controllers\Api\Seller\ReviewController as SellerReviewController;
use App\Http\Controllers\Api\Seller\ShopController as SellerShopController;
use App\Http\Controllers\Api\Seller\MessageController as SellerMessageController;
use App\Http\Controllers\Api\Buyer\HomeController;
use App\Http\Controllers\Api\Buyer\SearchController;
use App\Http\Controllers\Api\Buyer\CartController;
use App\Http\Controllers\Api\Buyer\NotificationController;
use App\Http\Controllers\Api\Buyer\OrderController;
use App\Http\Controllers\Api\Buyer\ReviewController;
use App\Http\Controllers\Api\Buyer\CategoryController;
use App\Http\Controllers\Api\Buyer\ProductController;
use App\Http\Controllers\Api\Buyer\ShopController;
use App\Http\Controllers\Api\Buyer\MessageController;
use App\Http\Controllers\Api\Buyer\AddressController;
// use App\Http\Controllers\Api\Buyer\LoyaltyPointController;
use App\Http\Controllers\Api\Buyer\BannerController;
use App\Http\Controllers\Api\Buyer\ShippingMethodController;
use App\Http\Controllers\Api\Buyer\VoucherController;
use App\Http\Controllers\Api\Buyer\UserController;
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
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');
        Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{id}/status', [AdminUserController::class, 'updateStatus'])->name('users.updateStatus');

        // Shop management
        Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
        Route::put('/shops/{shop}/status', [AdminShopController::class, 'updateStatus'])->name('shops.updateStatus');
        Route::delete('/shops/{shop}', [AdminShopController::class, 'destroy'])->name('shops.destroy');

        // Product management
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/{id}', [AdminProductController::class, 'show'])->name('products.show');
        Route::patch('/products/{id}/status', [AdminProductController::class, 'updateStatus'])->name('products.updateStatus');
        Route::patch('/variants/{id}/status', [AdminProductController::class, 'updateVariantStatus'])->name('variants.updateStatus');
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        // Category management
        Route::get('categories', [AdminCategoryController::class, 'index']);       
        Route::get('categories/{categoryId}/attributes', [AdminCategoryController::class, 'attributes']);

        // Order management
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{id}/settled-status', [AdminOrderController::class, 'updateSettledStatus'])->name('orders.updateSettledStatus');
        Route::put('/orders/{id}/shipping-status', [AdminOrderController::class, 'updateShippingStatus'])->name('orders.updateShippingStatus');
        Route::put('/orders/{id}/order-status', [AdminOrderController::class, 'updateOrderStatus'])->name('orders.updateOrderStatus');
        Route::delete('/orders/{id}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');

        // Dispute management
        Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{id}', [DisputeController::class, 'show'])->name('disputes.show');
        Route::put('/disputes/{id}/status', [DisputeController::class, 'updateStatus'])->name('disputes.updateStatus');
        Route::delete('/disputes/{id}', [DisputeController::class, 'destroy'])->name('disputes.destroy');

        // Voucher management
        Route::apiResource('vouchers', AdminVoucherController::class)->names([
            'index' => 'vouchers.index',
            'store' => 'vouchers.store',
            'show' => 'vouchers.show',
            'update' => 'vouchers.update',
            'destroy' => 'vouchers.destroy',
        ]);

        // Shipping partner management
        Route::get('shipping-partners', [AdminShippingPartnerController::class, 'index'])->name('shipping-partners.index');
        Route::get('shipping-partners/all', [AdminShippingPartnerController::class, 'all'])->name('shipping-partners.all');
        Route::post('shipping-partners', [AdminShippingPartnerController::class, 'store'])->name('shipping-partners.store');
        Route::get('shipping-partners/{id}', [AdminShippingPartnerController::class, 'show'])->name('shipping-partners.show');
        Route::put('shipping-partners/{id}', [AdminShippingPartnerController::class, 'update'])->name('shipping-partners.update');
        Route::delete('shipping-partners/{id}', [AdminShippingPartnerController::class, 'destroy'])->name('shipping-partners.destroy');

        // Payment management
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::put('/payments/{id}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::put('/payments/{id}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');

        // Review management
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{shopId}', [AdminReviewController::class, 'showReviews'])->name('reviews.showReviews');

        // Report management
        Route::get('/reports', [ReportController::class, 'index']);
        Route::post('/reports/generate', [ReportController::class, 'generateReports']);

        // Banner management
        Route::apiResource('/banners', AdminBannerController::class)->names([
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
    Route::post('/register', [AuthController::class, 'registerSeller']);

    Route::middleware(['auth:sanctum', 'role:seller'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/orders/revenue', [SellerOrderController::class, 'revenue']);
        Route::get('/orders/export-report', [SellerOrderController::class, 'exportReport']);
        Route::get('/orders', [SellerOrderController::class, 'index']);
        Route::get('/orders/{id}', [SellerOrderController::class, 'show'])->where('id', '[0-9]+');
        Route::get('/shipping-partners', [SellerShippingPartnerController::class, 'index']);
        Route::post('/shipping-partners/toggle', [SellerShippingPartnerController::class, 'toggle']);
        Route::get('/products', [SellerProductController::class, 'index']);
        Route::get('/products/{id}', [SellerProductController::class, 'show']);
        Route::post('/products', [SellerProductController::class, 'store']);
        Route::put('/products/{id}', [SellerProductController::class, 'update']);
        Route::delete('/products/{id}', [SellerProductController::class, 'destroy']);
        Route::put('variants/{variantId}/status', [SellerProductController::class, 'updateVariantStatus']);
        Route::get('categories', [SellerCategoryController::class, 'index']);
        Route::get('categories/{categoryId}/attributes', [SellerCategoryController::class, 'attributes']);
        Route::get('/reviews', [SellerReviewController::class, 'showReviews'])->name('seller.reviews.index');
        Route::patch('/reviews/{id}/hide', [SellerReviewController::class, 'hide'])->name('seller.reviews.hide');
        Route::get('/shop/profile', [SellerShopController::class, 'profile']);
        Route::post('/shop/profile', [SellerShopController::class, 'updateProfile']);
        Route::get('/shop/settings', [SellerShopController::class, 'settings']);
        Route::post('/shop/settings', [SellerShopController::class, 'updateSettings']);
        // Trong routes/api.php, nhóm middleware auth:sanctum, role:seller
        Route::get('/shop/decoration', [SellerShopController::class, 'decoration']);
        Route::post('/shop/decoration/banners', [SellerShopController::class, 'storeBanner']);
        Route::put('/shop/decoration/banners/{id}', [SellerShopController::class, 'updateBanner']);
        Route::delete('/shop/decoration/banners/{id}', [SellerShopController::class, 'deleteBanner']);
        Route::get('/messages', [SellerMessageController::class, 'index']);
        Route::get('/messages/detail', [SellerMessageController::class, 'show']); // Sử dụng query parameter ?buyer_id
        Route::post('/messages/send', [SellerMessageController::class, 'send']);
        Route::put('/messages/{buyerId}/read', [SellerMessageController::class, 'markAsRead']);
        //  Route::get('/buyers', [SellerMessageController::class, 'getBuyers']);

    });
});

Route::prefix('buyer')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'registerBuyer']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Public routes
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/search', [SearchController::class, 'search'])->middleware('auth:sanctum');
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/shops/{id}', [ShopController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:buyer,seller'])->group(function () {
        Route::get('/user', [UserController::class, 'getUser']);
        Route::post('/user', [UserController::class, 'updateUser']);
        
        Route::get('/search-history', [SearchController::class, 'history']);
        Route::delete('/search-history/{id}', [SearchController::class, 'deleteSearchHistory']);
        Route::delete('/search-history-delete', [SearchController::class, 'clearSearchHistory']);
        
        Route::get('/cart', [CartController::class, 'index']);
        Route::get('/cart/count', [CartController::class, 'count']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::put('/cart/{id}', [CartController::class, 'update']);
        Route::delete('/cart/{id}', [CartController::class, 'destroy']);

        Route::get('/banners', [BannerController::class, 'getBanners'])->name('banners.index');

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/count', [NotificationController::class, 'count']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

        Route::get('/shipping-methods', [ShippingMethodController::class, 'index']);

        Route::get('/vouchers/available', [VoucherController::class, 'available']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders/create', [OrderController::class, 'create']);
        Route::post('/orders/bulk-create', [OrderController::class, 'bulkCreate']);

        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::get('/reviews/check-order/{orderId}', [ReviewController::class, 'checkOrderReviewStatus']);

        Route::post('/shops/{id}/follow', [ShopController::class, 'follow']);
        Route::delete('/shops/{id}/unfollow', [ShopController::class, 'unfollow']);
        Route::post('/shops/{id}/dispute', [ShopController::class, 'createDispute'])->name('shops.dispute');

        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages/send', [MessageController::class, 'send']);
        Route::put('/messages/{id}/read', [MessageController::class, 'markAsRead']);

        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{id}', [AddressController::class, 'update']);
        Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
        Route::put('/addresses/{id}/set-default', [AddressController::class, 'setDefault']);

        // Route::get('/loyalty-points', [LoyaltyPointController::class, 'index']);

        Route::get('/chats', [MessageController::class, 'index']);
        Route::get('/chats/detail', [MessageController::class, 'show']);
        Route::post('/chats/send', [MessageController::class, 'send']);
        Route::put('/chats/{sellerId}/read', [MessageController::class, 'markAsRead']);
        Route::get('/sellers', [MessageController::class, 'getSellers']);
    });
});