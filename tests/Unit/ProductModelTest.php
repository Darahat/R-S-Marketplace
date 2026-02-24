<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class ProductModelTest extends TestCase{
    use RefreshDatabase;
    public function test_product_has_correct_fillable_attributes(){
    /// Arrange
    $product = new Product;
    // Act
    $fillable = $product->getFillable();
    $this->assertEquals(
        [    'name',
        'slug',
        'description',
        'price',
        'purchase_price',
        'discount_price',
        'sold_count',
        'featured',
        'image',
        'category_id',
        'brand_id',
        'stock',
        'is_best_selling',
        'is_latest',
        'is_flash_sale',
        'is_todays_deal',
        'rating'], $fillable
    );
    }
    function test_products_belongs_to_a_category(){
        /// Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id
        ]);
        // Check relationship

        $this->assertInstanceOf(Category::class,$product->category);
        $this->assertTrue($product->category->is($category));
     }
     function test_product_belongs_to_brand(){
        // Arrange
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
        ]);

        $this->assertInstanceOf(Brand::class,$product->brand);
        $this->assertTrue($product->brand->is($brand));

     }
}
