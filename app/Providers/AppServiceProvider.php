<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

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
            $cart = session()->get('cart', []);
            $view->with('categories', $categories);
            $view->with('cart', $cart );
        });
  
    }
}