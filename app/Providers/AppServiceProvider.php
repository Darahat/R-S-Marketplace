<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Wishlist;
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
        Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));
        View::composer('frontend_view.components.shared.navigation_bar', function ($view) {
            $categories = Cache::remember('nav_categories', 60, function () {
                $allCategories = DB::table('categories')
                    ->where('status', true)
                    ->orderBy('name', 'asc')
                    ->get();

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
