<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        'status'], $fillable
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
    public function test_brand_has_many_products(){
        /// Arrange: create a brand
        $brand = Brand::factory()->create();

        // Act: Create 3 products for this brand
        $products = Product::factory()->count(3)->create([
            'brand_id' => $brand->id,
        ]);

        // Assert: Check the Relatinoship
        $this->assertCount(3, $brand->products);
        $this->assertInstanceOf(Product::class, $brand->products);
    }
}
