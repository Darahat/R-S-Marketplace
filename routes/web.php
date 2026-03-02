<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;
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
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;
use Prometheus\Storage\InMemory;
use Prometheus\RenderTextFormat;
use App\Http\Controllers\Api\CustomerProfileApiController;

Route::get('/metrics', function () {
    // Use Redis storage if PHP Redis extension is available, else fallback to InMemory
    if (class_exists('\\Redis')) {
        $registry = new CollectorRegistry(new Redis([
            'host' => '127.0.0.1',
            'port' => 6379,
        ]));
    } else {
        $registry = new CollectorRegistry(new InMemory());
    }
    $renderer = new RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());

    return response($result, 200)
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category');
Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product');
Route::get('/search', [HomeController::class, 'search'])->name('search');



// Cart routes
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'view'])->name('cart.view');
    Route::get('/refresh/view', [CartController::class, 'refreshView'])->name('cart.view.refresh');
    Route::post('/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/remove', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/refresh', function () {
        return view('frontend_view.components.cards.cartDropdown');
    })->name('cart.refresh');
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
    Route::post('/checkout/{orderNumber}/complete-payment', [CheckoutController::class, 'completePayment'])->name('checkout.complete_payment');
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
    return view('errors.page-not-found');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('checklogin');

// Admin Login
Route::get('/admin-login', [AuthController::class, 'adminLogin'])->name('admin.login');
Route::post('/admin-login', [AuthController::class, 'adminLogin'])->name('admin.checklogin');

// Logout - Support both GET and POST
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
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
    Route::get('/dashboard', [DashboardController::class, 'customer_dashboard'])->name('customer.dashboard');
    Route::get('/profile-setting', [DashboardController::class, 'customer_profile_setting'])->name('customer.profile_setting');
    Route::post('/profile/update', [CustomerProfileApiController::class, 'update'])->name('customer.profile.update');
    Route::get('/profile/photo', [CustomerProfileApiController::class, 'instant_photo_view'])->name('customer.profile.photo');
    Route::get('/order-details/{id}', [DashboardController::class, 'customer_order_details'])->name('customer.order_details');
    Route::get('/order-history', [DashboardController::class, 'customer_order_history'])->name('customer.orders');

    // CUSTOMER ROUTES - Full resource routes
    Route::resource('addresses', AddressController::class)->except(['allAddressList']);

    // Payment Methods Management (Saved Cards)
    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('customer.payment_methods.index');
        Route::get('/saved', [PaymentMethodController::class, 'getSavedMethods'])->name('customer.payment_methods.saved');
        Route::post('/{id}/set-default', [PaymentMethodController::class, 'setDefault'])->name('customer.payment_methods.set_default');
        Route::delete('/{id}', [PaymentMethodController::class, 'destroy'])->name('customer.payment_methods.destroy');
    });
});
    Route::get('addresses/setDefault', [AddressController::class, 'setDefault'])->name('addresses.setDefault');



Route::get('/clear-cache', function() {
	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('config:cache');
	Artisan::call('view:clear');
	 return "Cleared!";
});
