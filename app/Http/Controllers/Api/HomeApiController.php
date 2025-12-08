<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeApiController extends Controller
{
    /**
     * Get homepage data
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $allCategories = DB::table('categories')
                ->where('status', true)
                ->orderBy('name', 'asc')
                ->get();

            // Group by parent_id
            $categories = $allCategories->whereNull('parent_id');
            $subcategories = $allCategories->whereNotNull('parent_id');

            // Attach subcategories to each main category
            foreach ($categories as $category) {
                $category->subcategories = $subcategories->where('parent_id', $category->id)->values();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'latest_products' => DB::table('products')
                        ->orderBy('created_at', 'desc')
                        ->take(8)
                        ->get(),
                    
                    'best_selling_products' => DB::table('products')
                        ->orderBy('sold_count', 'desc')
                        ->take(8)
                        ->get(),
                    
                    'discount_products' => DB::table('products')
                        ->where('discount_price', '>', 0)
                        ->take(8)
                        ->get(),
                    
                    'regular_products' => DB::table('products')
                        ->inRandomOrder()
                        ->take(8)
                        ->get(),
                    
                    'suggested_products' => DB::table('products')
                        ->where('featured', true)
                        ->take(8)
                        ->get(),
                    
                    'categories' => $categories,
                    'all_categories' => $allCategories,
                ],
                'message' => 'Homepage data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Home API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve homepage data'
            ], 500);
        }
    }

    /**
     * Get products by category
     * 
     * @param string $slug Category slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function category($slug)
    {
        try {
            $category = DB::table('categories')->where('slug', $slug)->first();
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $brands = DB::table('brands')
                ->where('status', true)
                ->orderBy('name', 'asc')
                ->get();

            $allCategories = DB::table('categories')
                ->where('status', true)
                ->orderBy('name', 'asc')
                ->get();

            // Group by parent_id
            $categories = $allCategories->whereNull('parent_id');
            $subcategories = $allCategories->whereNotNull('parent_id');

            // Attach subcategories to each main category
            foreach ($categories as $cat) {
                $cat->subcategories = $subcategories->where('parent_id', $cat->id)->values();
            }

            $products = DB::table('products')
                ->where('category_id', $category->id)
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $category,
                    'products' => $products,
                    'brands' => $brands,
                    'categories' => $categories,
                    'all_categories' => $allCategories,
                ],
                'message' => 'Category products retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Category API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category products'
            ], 500);
        }
    }
}