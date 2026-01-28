<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\UserPaymentMethod;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Policies\PaymentMethodPolicy;
use App\Policies\UserAddressPolicy;
use App\Policies\BrandPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(UserPaymentMethod::class, PaymentMethodPolicy::class);
        Gate::policy(Address::class, UserAddressPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);

        // Register API Routes
        Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));

        // Share data with navigation view
        View::composer('frontend_view.components.shared.navigation_bar', function ($view) {
            $categories = Cache::remember('nav_categories', 60, function () {
                $allCategories = Category::where('status',true)->orderBy('name', 'asc')->get();


                $categories = $allCategories->whereNull('parent_id')->values();
                $subcategories = $allCategories->whereNotNull('parent_id');

                // Attach subcategories
                foreach ($categories as $category) {
                    $category->subcategories = $subcategories
                        ->where('parent_id', $category->id)
                        ->values();
                }

                return $categories;
            });

            // Get cart count based on authentication
            if (Auth::check()) {
                $userCart = Cart::where('user_id', Auth::id())->first();
                $cartCount = $userCart ? $userCart->items->sum('quantity') : 0;
            } else {
                $cart = session()->get('cart', []);
                $cartCount = collect($cart)->sum('quantity');
            }

            // Get wishlist count based on authentication
            if (Auth::check()) {
                $userWishlist = Wishlist::where('user_id', Auth::id())->first();
                $wishlistCount = $userWishlist ? $userWishlist->items->count() : 0;
            } else {
                $wishlistCount = count(session('wishlist', []));
            }

            $view->with('categories', $categories);
            $view->with('cartCount', $cartCount);
            $view->with('wishlistCount', $wishlistCount);
        });

    }
}
