<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
class BrandModelTest extends TestCase{
    // it will auto remove all data created on db
    use RefreshDatabase;

    public function test_brand_has_correct_fillable_attributes(){
    // Arrange
    $brand = new Brand();
    // Act
    $fillable = $brand->getFillable();
    // Assert
    $this->assertEquals(
        ['name',
        'slug',
        'category_id',
        'status',
        'logo'], $fillable
    );
    }
    public function test_can_create_brand_using_factory(){
    //Act
    $brand = Brand::factory()->create([
        'name' => 'Nike',
        'slug' => 'nike',
    ]);
     //Assert: Check it was created
     $this->assertDatabaseHas('brands',['name' => 'Nike',
        'slug' => 'nike',]);

    $this->assertEquals('Nike', $brand->name);
    }
    public function test_brand_has_many_products()
    {
        /// Arrange: create a brand
        $brand = Brand::factory()->create();

        // Act: Create 3 products for this brand
        $products = Product::factory()->count(3)->create([
            'brand_id' => $brand->id,
        ]);

        // Assert: Check the Relationship
        $this->assertCount(3, $brand->products);
        $this->assertInstanceOf(Product::class, $brand->products->first());
    }
    public function test_brand_returns_correct_categories(){
        // Arrange: Create Categories
        $category1 = Category::factory()->create(['id' => 5]);
        $category2 = Category::factory()->create(['id' => 10]);

        // Create brand with those category IDs
        $brand = Brand::factory()->create([
            'category_id' => '5,10',
        ]);
        // Act: Get categories
        $categories = $brand->categories();

        //Assert: Should have 2 categories
        $this->assertCount(2, $categories);
        $this->assertTrue($categories->contains('id', 5));
        $this->assertTrue($categories->contains('id', 10));
    }
    public function test_brand_without_categories_returns_empty(){
        // Arrange: Create brand with null category_id
        $brand = Brand::factory()->create([
            'category_id' => null,
        ]);
        // Act
        $categories = $brand->categories();

        // Assert
        $this->assertCount(0, $categories);
    }
    public function test_brand_complete_lifecycle_with_all_best_practices(){
        /// Descriptive test name
        /// Arrange-Act-Assert patter
        /// Test isolation(RefreshDatabase)
        // Using factories, not row data

        // Arrange: Set up initial state
        $categoryIds = Category::factory()->count(2)->create()->pluck('id')->implode(',');
        Mail::fake(); // Using fakes

        //Act: Perform the action
        $brand = Brand::factory()->create([
            'name' => 'Premium Brand',
            'category_id' => $categoryIds,
            'status' => true,
        ]);

        // Assert: Multiple meaningful assertions
        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertTrue($brand->status);
        $this->assertCount(2, $brand->categories());

        // Act: Test state change
        $brand->status = false;
        $brand->save();

        //  Assert: verify change
        $this->assertFalse($brand->fresh()->status);

        // Act: Test deletion
        $brandId = $brand->id;
        $brand->delete();

        // Assert: Verify deletion
        $this->assertDatabaseMissing('brands',['id' => $brandId]);

        /// Test is self-contained
        /// Test is readable
        /// Test is maintainable
    }
}
