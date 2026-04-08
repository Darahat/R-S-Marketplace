<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProductSettingController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Admin\HeroSectionController;
use App\Http\Controllers\PaymentProcessController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Customer\CustomerProfileController;



Route::get('/test', function () {
    return session()->getId();
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category');
Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/support', function () {
    return view('frontend_view.pages.support', [
        'data' => ['title' => 'Customer Support'],
    ]);
})->name('support');
Route::get('/return-policy', function () {
    return view('frontend_view.pages.return_policy', [
        'data' => ['title' => 'Return Policy'],
    ]);
})->name('return.policy');



// Cart routes
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'view'])->name('cart.view');
    Route::get('/refresh/view', [CartController::class, 'refreshView'])->name('cart.view.refresh');
    Route::post('/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/refresh', [CartController::class, 'cartRefresh'])->name('cart.refresh');


});

// Checkout routes
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buy.now');
    Route::post('/checkout/review', [CheckoutController::class, 'review'])->name('checkout.review');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/process', [PaymentProcessController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

    Route::get('/checkout/to-pay', [CheckoutController::class, 'toPayOrders'])->name('checkout.to_pay');
    Route::post('/checkout/{orderNumber}/complete-payment', [PaymentProcessController::class, 'completePayment'])->name('checkout.complete_payment');

        Route::get('/notifications/unread-count', function () {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count()
        ]);
    });

    // Get all unread notifications
    Route::get('/notifications', function () {
        return response()->json(
            auth()->user()->unreadNotifications()->latest()->take(10)->get()
        );
    });

    // Mark one as read
    Route::post('/notifications/{id}/read', function ($id) {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['success' => true]);
    });

    // Mark all as read
    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    });
});

// Stripe webhook route (must be outside auth middleware)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Wishlist routes
Route::prefix('wishlist')->group(function () {
    Route::get('/', [WishlistController::class, 'view'])->name('wishlist.view');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::post('/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::get('/count', [WishlistController::class, 'getCount'])->name('wishlist.count');
});

// Guest/Customer Login
// Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', function () {
    return view('frontend_view.pages.auth.login');
})->name('login');
Route::post('/login', [CustomerAuthController::class, 'login'])->name('checklogin');

// Test route - remove this later
Route::get('/admin-login-test', function() {
    Log::info('Test route hit!');
    return 'Test route works! User: ' . (Auth::check() ? Auth::user()->email : 'guest');
});

// Admin Login - separate GET and POST
Route::get('/admin-login', [AdminAuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin-login', [AdminAuthController::class, 'adminLogin'])->name('admin.checklogin');

// Logout - POST only to avoid accidental logout/session invalidation from GET requests
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register']);
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:web', 'isAdmin']], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Product Management
     Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    // Category Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('admin.categories.show');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::post('/categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('admin.categories.toggleStatus');
    Route::post('/categories/{id}/toggle-featured', [CategoryController::class, 'toggleFeatured'])->name('admin.categories.toggleFeatured');
    Route::get('/api/categories/tree', [CategoryController::class, 'getTree'])->name('admin.categories.tree');

    // Product Management
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('admin.products.show');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/products/{id}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('admin.products.toggleFeatured');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('admin.products.bulk-delete');

    // Order Management
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::post('/orders/{id}/update-payment-status', [OrderController::class, 'updatePaymentStatus'])->name('admin.orders.update-payment-status');
    Route::post('/orders/{id}/update-notes', [OrderController::class, 'updateNotes'])->name('admin.orders.update-notes');
    Route::get('/orders/{id}/print', [OrderController::class, 'printInvoice'])->name('admin.orders.print');
    Route::get('/api/orders/statistics', [OrderController::class, 'getStatistics'])->name('admin.orders.statistics');

    // Payment Management
    Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments');
    Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('admin.payments.show');
    Route::post('/payments/{id}/process', [PaymentController::class, 'process'])->name('admin.payments.process');
    Route::post('/payments/{id}/mark-failed', [PaymentController::class, 'markFailed'])->name('admin.payments.mark-failed');
    Route::post('/payments/{id}/refund', [PaymentController::class, 'refund'])->name('admin.payments.refund');
    Route::post('/payments/{id}/update-notes', [PaymentController::class, 'updateNotes'])->name('admin.payments.update-notes');
    Route::get('/api/payments/statistics', [PaymentController::class, 'getStatistics'])->name('admin.payments.statistics');
    Route::get('/api/payments/trends', [PaymentController::class, 'getTrends'])->name('admin.payments.trends');
    Route::get('/api/payments/method-breakdown', [PaymentController::class, 'getMethodBreakdown'])->name('admin.payments.method-breakdown');

    // Hero Section
    Route::get('/hero', [HeroSectionController::class, 'edit'])->name('admin.hero.edit');
    Route::post('/hero', [HeroSectionController::class, 'update'])->name('admin.hero.update');

    // User Management
    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{id}', [UserManagementController::class, 'show'])->name('admin.users.show');
    Route::post('/users/{id}/update-role', [UserManagementController::class, 'updateRole'])->name('admin.users.update-role');

    // ADMIN ROUTES - Resource except create/store/setDefault
    Route::resource('addresses', AddressController::class)
        ->names('admin.addresses')
        ->except(['index','create', 'store','setDefault']);

        Route::get('addresses/getAll', [AddressController::class, 'allAddressList'])->name('addresses.getAll');


/// These routes user role managed by policy
// TODO: Every route should be here whoes role managed by policy
   // Brand Management
    Route::get('/brands', [BrandController::class, 'index'])->name('admin.brands.index');
    Route::get('/brands/create', [BrandController::class, 'create'])->name('admin.brands.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('admin.brands.store');
    Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])->name('admin.brands.edit');
    Route::put('/brands/{id}', [BrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('/brands/{id}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');
    Route::patch('/brands/{id}/toggle-status',[BrandController::class, 'toggleStatus'])->name('admin.brands.toggle-status');
});






Route::group(['prefix' => 'customer', 'middleware' => ['auth:web', 'isCustomer']], function () {
    Route::get('/dashboard', [DashboardController::class, 'customerDashboard'])->name('customer.dashboard');
    Route::get('/profile-setting', [DashboardController::class, 'customerProfileSetting'])->name('customer.profile_setting');
    Route::post('/profile/update', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
    Route::get('/profile/photo', [CustomerProfileController::class, 'instantPhotoView'])->name('customer.profile.photo');
    Route::get('/order-details/{orderNumber}', [DashboardController::class, 'customerOrderDetails'])->name('customer.order_details');
    Route::get('/order-history', [DashboardController::class, 'customerOrderHistory'])->name('customer.orders');
    Route::get('/wishlist', [WishlistController::class, 'customerWishlist'])->name('customer.wishlist');
    Route::get('/profile', [DashboardController::class, 'customerProfile'])->name('customer.profile');

    // CUSTOMER ROUTES - Full resource routes
    Route::resource('addresses', AddressController::class)
        ->names('customer.addresses')
        ->except(['allAddressList']);
    Route::get('addresses/setDefault', [AddressController::class, 'setDefault'])->name('customer.addresses.setDefault');

    // Payment Methods Management (Saved Cards)
    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('customer.payment_methods.index');
        Route::get('/saved', [PaymentMethodController::class, 'getSavedMethods'])->name('customer.payment_methods.saved');
        Route::post('/{id}/set-default', [PaymentMethodController::class, 'setDefault'])->name('customer.payment_methods.set_default');
        Route::delete('/{id}', [PaymentMethodController::class, 'destroy'])->name('customer.payment_methods.destroy');
    });
});



Route::get('/clear-cache', function() {
	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('config:cache');
	Artisan::call('view:clear');
	 return "Cleared!";
});

