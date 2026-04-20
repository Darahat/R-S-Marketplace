<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Mail;

class ProductRepositoryTest extends TestCase{
    use RefreshDatabase;
    protected ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    public function test_product_repository_exist(){
        $this->assertInstanceOf(ProductRepository::class, $this->repository);
    }

    public function test_product_can_be_searched_by(){
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $matchingProduct = Product::factory()->create([
            'name' => 'Gaming Laptop',
            'slug' => 'gaming-laptop',
            'description' => 'High performance laptop for gaming',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        Product::factory()->create([
            'name' => 'Office Chair',
            'slug' => 'office-chair',
            'description' => 'Comfortable chair for desk work',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        $results = $this->repository->getFilteredPaginatedProducts([
            'search' => 'gaming',
        ]);

        $this->assertCount(1, $results->items());
        $this->assertSame($matchingProduct->id, $results->items()[0]->id);
    }
}
