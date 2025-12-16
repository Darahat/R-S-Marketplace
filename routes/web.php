<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSettingController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\PaymentProcessController;


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
    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

    Route::get('/checkout/to-pay', [CheckoutController::class, 'toPayOrders'])->name('checkout.to_pay');
    Route::post('/checkout/{orderNumber}/complete-payment', [CheckoutController::class, 'completePayment'])->name('checkout.complete_payment');
});

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
Route::post('/login', [AuthController::class, 'login'])->name('checklogin');

// Admin Login
Route::get('/admin-login', [AuthController::class, 'adminLogin'])->name('admin.login');
Route::post('/admin-login', [AuthController::class, 'adminLogin'])->name('admin.checklogin');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth:web'], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Product Management
    Route::get('/viewProduct', [ProductController::class, 'viewProduct'])->name('admin.viewproduct');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Brand Management
    Route::get('/brands', [\App\Http\Controllers\BrandController::class, 'index'])->name('admin.brands.index');
    Route::get('/brands/create', [\App\Http\Controllers\BrandController::class, 'create'])->name('admin.brands.create');
    Route::post('/brands', [\App\Http\Controllers\BrandController::class, 'store'])->name('admin.brands.store');
    Route::get('/brands/{id}/edit', [\App\Http\Controllers\BrandController::class, 'edit'])->name('admin.brands.edit');
    Route::put('/brands/{id}', [\App\Http\Controllers\BrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('/brands/{id}', [\App\Http\Controllers\BrandController::class, 'destroy'])->name('admin.brands.destroy');
    Route::post('/brands/{id}/toggle-status', [\App\Http\Controllers\BrandController::class, 'toggleStatus'])->name('admin.brands.toggleStatus');

    // Category Management
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [\App\Http\Controllers\CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('admin.categories.show');
    Route::get('/categories/{id}/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::post('/categories/{id}/toggle-status', [\App\Http\Controllers\CategoryController::class, 'toggleStatus'])->name('admin.categories.toggleStatus');
    Route::post('/categories/{id}/toggle-featured', [\App\Http\Controllers\CategoryController::class, 'toggleFeatured'])->name('admin.categories.toggleFeatured');
    Route::get('/api/categories/tree', [\App\Http\Controllers\CategoryController::class, 'getTree'])->name('admin.categories.tree');

    // Product Management
    Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'show'])->name('admin.products.show');
    Route::get('/products/{id}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/products/{id}/toggle-featured', [\App\Http\Controllers\Admin\ProductController::class, 'toggleFeatured'])->name('admin.products.toggleFeatured');
    Route::post('/products/bulk-delete', [\App\Http\Controllers\Admin\ProductController::class, 'bulkDelete'])->name('admin.products.bulk-delete');

    // Order Management
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders');
    Route::get('/orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/orders/{id}/update-status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::post('/orders/{id}/update-payment-status', [\App\Http\Controllers\Admin\OrderController::class, 'updatePaymentStatus'])->name('admin.orders.update-payment-status');
    Route::post('/orders/{id}/update-notes', [\App\Http\Controllers\Admin\OrderController::class, 'updateNotes'])->name('admin.orders.update-notes');
    Route::get('/orders/{id}/print', [\App\Http\Controllers\Admin\OrderController::class, 'printInvoice'])->name('admin.orders.print');
    Route::get('/api/orders/statistics', [\App\Http\Controllers\Admin\OrderController::class, 'getStatistics'])->name('admin.orders.statistics');

    // Payment Management
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments');
    Route::get('/payments/{id}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('admin.payments.show');
    Route::post('/payments/{id}/process', [\App\Http\Controllers\Admin\PaymentController::class, 'process'])->name('admin.payments.process');
    Route::post('/payments/{id}/mark-failed', [\App\Http\Controllers\Admin\PaymentController::class, 'markFailed'])->name('admin.payments.mark-failed');
    Route::post('/payments/{id}/refund', [\App\Http\Controllers\Admin\PaymentController::class, 'refund'])->name('admin.payments.refund');
    Route::post('/payments/{id}/update-notes', [\App\Http\Controllers\Admin\PaymentController::class, 'updateNotes'])->name('admin.payments.update-notes');
    Route::get('/api/payments/statistics', [\App\Http\Controllers\Admin\PaymentController::class, 'getStatistics'])->name('admin.payments.statistics');
    Route::get('/api/payments/trends', [\App\Http\Controllers\Admin\PaymentController::class, 'getTrends'])->name('admin.payments.trends');
    Route::get('/api/payments/method-breakdown', [\App\Http\Controllers\Admin\PaymentController::class, 'getMethodBreakdown'])->name('admin.payments.method-breakdown');

    // Hero Section
    Route::get('/hero', [\App\Http\Controllers\Admin\HeroSectionController::class, 'edit'])->name('admin.hero.edit');
    Route::post('/hero', [\App\Http\Controllers\Admin\HeroSectionController::class, 'update'])->name('admin.hero.update');

});
Route::group(['prefix' => 'customer', 'middleware' => 'auth:web'], function () {
    Route::get('/dashboard', [DashboardController::class, 'customer_dashboard'])->name('customer.dashboard');
    Route::get('/profile-setting', [DashboardController::class, 'customer_profile_setting'])->name('customer.profile_setting');
    Route::post('/profile/update', [\App\Http\Controllers\Api\CustomerProfileApiController::class, 'update'])->name('customer.profile.update');
    Route::get('/profile/photo', [\App\Http\Controllers\Api\CustomerProfileApiController::class, 'instant_photo_view'])->name('customer.profile.photo');
    Route::get('/order-details/{id}', [DashboardController::class, 'customer_order_details'])->name('customer.order_details');
    Route::get('/order-history', [DashboardController::class, 'customer_order_history'])->name('customer.orders');

    // Address routes
    Route::get('/addresses', [AddressController::class, 'index'])->name('customer.address');
    Route::get('/addresses/list', [AddressController::class, 'index'])->name('customer.addresses.index');
    Route::get('/addresses/create', [AddressController::class, 'create'])->name('customer.addresses.create');
    Route::post('/addresses/store', [AddressController::class, 'store'])->name('customer.addresses.store');
    Route::get('/addresses/{address_id}/edit/{user_id}', [AddressController::class, 'edit'])->name('customer.addresses.edit');
    Route::put('/addresses/{address_id}/update/{user_id}', [AddressController::class, 'update'])->name('customer.addresses.update');
    Route::delete('/addresses/destory/{address_id}', [AddressController::class, 'destroy'])->name('customer.addresses.destroy');
    Route::post('/addresses/{address_id}/set-default/{user_id}', [AddressController::class, 'setDefault'])->name('customer.addresses.set-default');
});



Route::get('/clear-cache', function() {
	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('config:cache');
	Artisan::call('view:clear');
	 return "Cleared!";
});