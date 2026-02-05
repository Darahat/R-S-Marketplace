<?php
namespace Tests\Unit;


use Tests\TestCase;
use App\Repositories\BrandRepository;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrandRepositoryTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;
    protected BrandRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create repository instance before each test
        $this->repository = new BrandRepository();
    }

    public function test_repository_exists()
    {
        // Simple test to check repository instantiates
        $this->assertInstanceOf(BrandRepository::class, $this->repository);
    }


public function test_get_all_brands_returns_only_active_brands()
{
    // Arrange: Create brands with different statuses
    Brand::factory()->create(['name' => 'Nike', 'status' => true]);
    Brand::factory()->create(['name' => 'Adidas', 'status' => true]);
    Brand::factory()->create(['name' => 'Inactive Brand', 'status' => false]);

    // Act: Get all brands
    $brands = $this->repository->getAllBrands();

    // Assert: Should only get active brands (2 brands)
    $this->assertCount(2, $brands);
    $this->assertTrue($brands->contains('name', 'Nike'));
    $this->assertTrue($brands->contains('name', 'Adidas'));
    $this->assertFalse($brands->contains('name', 'Inactive Brand'));
}
public function test_get_all_brands_returns_ordered_by_name(){
        // Arrange: Create brands with different statuses
    Brand::factory()->create(['name' => 'Zara', 'status' => true]);
    Brand::factory()->create(['name' => 'Apple', 'status' => true]);
    Brand::factory()->create(['name' => 'Microsoft', 'status' => true]);

     // Act: Get all brands
    $brands = $this->repository->getAllBrands();

    // Assert: Check alphabetical order
    $this->assertEquals('Apple', $brands->first()->name);
    $this->assertEquals('Zara', $brands->last()->name);
}
public function test_create_brand_successfully(){
    // Arrange: Prepare brand data
    $brandData = [
         'name' => 'Samsung',
        'slug' => 'samsung',
        'category_id' => '1,2,3',
        'status' => true,
    ];
    // Act: Create brand
    $brand = $this->repository->createBrand($brandData);

    // Assert: Check it was created
    $this->assertInstanceOf(Brand::class, $brand);
    $this->assertEquals('Samsung', $brand->name);

    //Check it's in database
    $this->assertDatabaseHas('brands',[
         'name' => 'Samsung',
        'slug' => 'samsung',
    ]);
}
public function test_find_brand_returns_correct_brand(){
    // Arrange: Create a brand
    $brand = Brand::factory()->create(['name' => 'Sony']);

    // Act: find it
    $foundBrand = $this->repository->findBrand($brand->id);

    // Assert
    $this->assertNotNull($foundBrand);
    $this->assertEquals('Sony', $foundBrand->name);
    $this->assertEquals($brand->id, $foundBrand->id);
}
public function test_find_brand_returns_null_when_not_found(){
    // Act: Try to find non-existent brand
    $foundBrand = $this->repository->findBrand(9999);

    // Assert: Should return null
    $this->assertNull($foundBrand);
}
public function test_update_brand_successfully(){
    // Arrange: Create a brand
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);
    // Act: Update it
    $updateData = [
        'name' => 'New name',
        'slug' => 'new-name',
    ];
    $result = $this->repository->updateBrand($updateData, $brand->id);

    // Assert: Check update succeeded
    $this->assertTrue($result);

    // Check database has new values
    $this->assertDatabaseHas('brands', [
        'id' => $brand->id,
        'name' => 'New Name',
        'slug' => 'new-name',
    ]);

}

public function test_destroy_brand_successfully(){
    // Arrange: Crate a brand
    $brand = Brand::factory()->create(['name' =>'To Delete']);
    // Act: Delete it
    $result = $this->repository->destroyBrand($brand->id);

    // Assert: Check deletion succeeded
    $this->assertTrue($result);

    // Check its no longer in database
    $this->assertDatabaseMissing('brands',[
        'id' => $brand->id,
        'name' => 'To Delete',
    ]);
}
public function test_get_all_category_returns_only_active_categories(){
    // Arrange: Create categories
    Category::factory()->create(['name' => 'Electronics', 'status' => true]);
    Category::factory()->create(['name' => 'Fashion', 'status' => true]);
    Category::factory()->create(['name' => 'Inactive Cat', 'status' => false]);

    // Act
    $categories = $this->repository->getAllCategory();

    // Assert
    $this->assertCount(2, $categories);
    $this->assertTrue($categories->contains('name', 'Electronics'));
    $this->assertFalse($categories->contains('name', 'Inactive Cat'));


}
 // all of these test function before this function or only this function.this function is equal to all
public function test_full_crud_cycle()
{
    // Create
    $brand = $this->repository->createBrand([
        'name' => 'Test Brand',
        'slug' => 'test-brand',
        'category_id' => '1',
        'status' => true,
    ]);
    $this->assertNotNull($brand);

    // Read
    $found = $this->repository->findBrand($brand->id);
    $this->assertEquals('Test Brand', $found->name);

    // Update
    $updated = $this->repository->updateBrand([
        'name' => 'Updated Brand',
    ], $brand->id);
    $this->assertTrue($updated);

    // Verify update
    $found = $this->repository->findBrand($brand->id);
    $this->assertEquals('Updated Brand', $found->name);

    // Delete
    $deleted = $this->repository->destroyBrand($brand->id);
    $this->assertTrue($deleted);

    // Verify deletion
    $found = $this->repository->findBrand($brand->id);
    $this->assertNull($found);
}
}

