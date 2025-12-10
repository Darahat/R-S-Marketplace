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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category');
Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product');



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
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
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
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('checklogin');

// Admin Login
Route::get('/admin-login', [AuthController::class, 'adminLogin'])->name('admin.login');
Route::post('/admin-login', [AuthController::class, 'adminLogin'])->name('admin.checklogin');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth:web'], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Product
    Route::get('/viewProduct', [ProductController::class, 'viewProduct'])->name('admin.viewproduct');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    //Brand
    Route::get('/viewBrand', [ProductSettingController::class, 'viewBrand'])->name('admin.viewBrand');
    Route::delete('/brands/{id}', [ProductSettingController::class, 'destroy'])->name('brands.destroy');


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
