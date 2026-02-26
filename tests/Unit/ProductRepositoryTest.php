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

    public function test_product_can_be_searched_by(){}
}
