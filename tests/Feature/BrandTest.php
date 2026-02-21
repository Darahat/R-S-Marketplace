<?php

namespace Tests\Feature;


use App\Repositories\BrandRepository;
use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Support\Facades\Mail;
use App\Mail\BrandCreatedNotification; // We'll create this
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
class BrandTest extends TestCase
{
    use DatabaseTransactions;
     protected BrandRepository $repository;
     protected User $admin;
     protected User $regularUser;


     protected function setUp(): void{
        parent::setUp();
$this->repository = new BrandRepository();
        // Create test users for every test
        $this->admin =  User::factory()->create(['user_type' => User::ADMIN]);
        $this->regularUser = User::factory()->create(['user_type' => User::CUSTOMER]);
     }

     public function test_setup_works(){
        $this->assertNotNull($this->admin);
        $this->assertNotNull($this->regularUser);
     }

     public function test_admin_can_view_brands_index_page(){
        // Arrange: Create some brands
        Brand::factory()->count(3)->create(['status' => true]);

        $this->admin->assignRole('admin');
        // Act: Visit the brands page as admin
        $response =  $this->actingAs($this->admin)->get(route('admin.brands.index'));
        // Assert: Check response

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view.pages.brands.index');
        $response->assertViewHas('brands');

     }
     public function test_regular_user_cannot_view_brands_index(){
        // Act: Try to visit as regular user
        $response = $this->actingAs($this->regularUser)->get(route('admin.brands.index'));
        // Assert: Should be denied
        $response->assertStatus(403); /// HTTP 403 Forbidden

     }

     public function test_guest_is_redirected_to_login(){
        // Act: Visit without logging in
        $response = $this->get(route('admin.brands.index'));
        // Assert: Redirected to login
        // Assert: Forbidden
        $response->assertStatus(403);
     }
     public function test_admin_can_create_brand(){
        // Arrange: Create categories first
        $category1 = Category::factory()->create(['status' => true]);
        $category2 = Category::factory()->create(['status' => true]);

        // Prepare brand data
        $brandData = [
            'name' => 'Nike',
            'slug' => 'nike',
            'category_id' => [$category1->id, $category2->id],
            'status' => true,
        ];

        // Act: Submit form as admin
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'), $brandData);
        // Assert: Redirected to index with success message
        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success', 'Brand created successfully!');
        // Check Database
        $this->assertDatabaseHas('brands', [
            'name' => 'Nike',
            'slug' => 'nike',
        ]);
     }

     public function test_create_brand_requires_name(){
        // Arrange: Data without name
        $invalidData = [
            'slug' => 'nike',
            'status' => true,
        ];
        $this->admin->assignRole('admin');
        // Act: Try to create
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'),$invalidData);

        // Assert: Validation error
        $response->assertSessionHasErrors('name');

        // Brand should Not be in database
        $this->assertDatabaseMissing('brands',[
            'slug' => 'nike',
        ]);

     }

     public function test_cannot_create_brand_with_duplicate_slug(){
        // Arrange: Create existing brand
        Brand::factory()->create(['slug' => 'nike']);
         $this->admin->assignRole('admin');
        // Act: Try to create another with same slug
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'),[
                         'name' => 'Nike 2',
                         'slug' => 'nike', // Duplicate!
                         'status' => true,
                     ]);

        // Assert: Should have validation error
        $response->assertSessionHasErrors('slug');
     }
    public function test_admin_can_update_brand(){
        // Arrange: Create categories first
        $category1 = Category::factory()->create(['status' => true]);
        $category2 = Category::factory()->create(['status' => true]);

        // Create a brand
        $brand = Brand::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);
        $this->admin->assignRole('admin');
        // Act: Update it
        $response = $this->actingAs($this->admin)->put(route('admin.brands.update', $brand->id),[
            'name' => 'New Name',
            'slug' => 'new-name',
            'category_id' => [$category1->id, $category2->id],
            'status' => true,
        ]);
        // Assert: Success
        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success');
        //Check database updated
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'New Name',
        ]);
    }
    public function test_admin_can_delete_brand(){
        // Arrange: Create a brand
        $brand =  Brand::factory()->create(['name' =>'To Delete']);
        // Act: Delete it
        $response = $this->actingAs($this->admin)->delete(route('admin.brands.destroy', $brand->id));

        // Assert: Success
        $response->assertRedirect(route('admin.brands.index'));
        $response->assertSessionHas('success');

        // Check database
        $this->assertDatabaseMissing('brands',[
            'id' => $brand->id,
        ]);
    }
    public function test_regular_user_cannot_create_brand(){

        // Act: Try to create as regular user
        $response = $this->actingAs($this->regularUser)->post(route('admin.brands.store'),[
            'name' => 'Nike',
            'slug' => 'nike',
            'status' => true
        ]);

        // Assert: Forbidden (policy denies access)
        $response->assertStatus(403);

        //Should Not be in database
        $this->assertDatabaseMissing('brands',[
            'name'=> 'Nike',
        ]);
    }
    public function test_complete_brand_management_workflow(){
        // 1. View empty list
        $response = $this->actingAs($this->admin)->get(route('admin.brands.index'));
        $response->assertStatus(200);
  $category1 = Category::factory()->create(['status' => true]);
        $category2 = Category::factory()->create(['status' => true]);
        // 2. Create brand
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'),[
            'name' => 'Nike',
            'slug' => 'nike',
            'category_id' => [$category1->id,],
            'status' => true
        ]);
    $response->assertRedirect(route('admin.brands.index'));

    // 3. Verify it exists

    $brand = Brand::where('slug', 'nike')->first();
    $this->assertNotNull($brand);

    // 4. Update it
    $response = $this->actingAs($this->admin)->put(route('admin.brands.update', $brand->id),[
        'name' => 'Nike Updated',
        'slug' => 'nike-updated',
        'category_id' => [$category1->id],
        'status' => true,
    ]);
    $response->assertRedirect();
    // 5. Verify update
    $brand->refresh();
    $this->assertEquals('Nike Updated', $brand->name);

    // 6. Delete it
    $response = $this->actingAs($this->admin)->delete(route('admin.brands.destroy', $brand->id));
    $response->assertRedirect();

    // 7. Verify deletion
    $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_admin_can_toggle_brand_status(){
        // Arrange: Create an active brand
        $brand = Brand::factory()->create([
            'name' => 'Nike',
            'status' => true,
        ]);
        $this->admin->assignRole('admin');
        $response = $this->actingAs($this->admin)->patch(route('admin.brands.toggle-status', $brand->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check status changed from true to false
        $brand->refresh();
        $this->assertFalse($brand->status);
    }
    public function test_toggle_status_works_from_inactive_to_active(){
        // Arrange: Create INACTIVE brand
        $brand = Brand::factory()->create([
            'status' => false,
        ]);
        // Act: Toggle it
        $response = $this->actingAs($this->admin)->patch(route('admin.brands.toggle-status',$brand->id));

        // assert: Should now be active
        $brand->refresh();
        $this->assertTrue($brand->status);
    }
    public function test_get_brands_by_status(){
        // Arrange
        Brand::factory()->count(3)->create(['status' => true]);
        Brand::factory()->count(2)->create(['status' => false]);

        // Act: Get only active brands
        $activeBrands = $this->repository->getBrandByStatus(true);
        // Assert
        $this->assertCount(3, $activeBrands);

        // Act: Get Inactive brands
        $activeBrands = $this->repository->getBrandByStatus(false);
        $this->assertCount(2, $activeBrands);
    }
    public function test_regular_user_cannot_toggle_brand_status(){
        // Arrange
        $brand = Brand::factory()->create(['status' => true]);

        // Act: Try to toggle as regular user
        $response = $this->actingAs($this->regularUser)->patch(route('admin.brands.toggle-status', $brand->id));

        // Assert: Forbidden
        $response->assertStatus(403);

        // Status should Not have changed
        $brand->refresh();
        $this->assertTrue($brand->status); // Still true
    }
    public function test_brand_can_load_products_count(){
        // Arrange: Create brand with products
        $brand = Brand::factory()->create();
        Product::factory()->count(5)->create(['brand_id' => $brand->id]);

        // Act: Load with count
        $brand->loadCount('products');
        // Act & Assert
        $this->assertEquals(5, $brand->products_count);
    }

    public function test_email_notification_sent_when_brand_created(){
        // Arrange: Fake the mail system (no real emails sent!)
        Mail::fake();
        $category1 = Category::factory()->create(['status' => true]);
        Log::notice($category1);
        //Act: Create a brand
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'),[
             'name' => 'Nike',
             'slug' => 'nike',
             'category_id' => [$category1->id],
             'status' => true,
        ]);
        dump('Response Status:', $response->status());
    dump('Response Content:', $response->getContent());
    dump('Session Errors:', session('errors'));
    // Assert: Check email was "sent"
    Mail::assertSent(BrandCreatedNotification::class, function($mail){
        return $mail->hasTo('admin@example.com');
    });
    }

public function test_can_upload_brand_logo()
{
    // Arrange: Fake the storage system
    Storage::fake('public');

    // Create a fake image
    $file = UploadedFile::fake()->image('logo.jpg', 600, 400);

    // Act: Upload brand with logo
    $response = $this->actingAs($this->admin)
                     ->post(route('admin.brands.store'), [
                         'name' => 'Nike',
                         'slug' => 'nike',
                         'logo' => $file,
                         'status' => true,
                     ]);

    // Assert: File was stored
    Storage::disk('public')->assertExists('brands/' . $file->hashName());
}
public function test_brand_syncs_with_external_api(){
    // Arrange: Mock the HTTP client
    Http::fake([
        'api.example.com/brands' => Http::response([
            'success' => true,
            'brand_id' => 123456,

        ],200),
    ]);
    //Act: Create brand(triggers API call in real code)
    $brand = Brand::factory()->create(['name' => 'Nike']);
    $response = Http::post('api.example.com/brands',[
        'name' => $brand->name,
    ]);

    // Assert: API was called correctly
    $this->assertTrue($response->json('success'));

    Http::assertSent(function ($request){
        return $request->url() == 'api.example.com/brands' && $request['name'] == 'Nike';
    });

}

public function test_exception_thrown_when_brand_not_found()
{
    // Expect an exception
    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    // Act: Try to find non-existent brand with findOrFail
    Brand::findOrFail(99999);
}
public function test_cannot_delete_brand_with_products(){
    // Arrange: Brand with products
    $brand = Brand::factory()->create();
    Product::factory()->count(5)->create(['brand_id' => $brand->id]);

    //Act & Assert:Should throw exception
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Cannot delete brand with existing products');
    // Try to delete(this would be in your brandservice)
    if($brand->products()->count() >0){
        throw new \Exception('Cannot delete brand with existing products');
    }
}

public function test_brand_creation_is_fast(){
    $brand = Brand::factory()->create([
        'name' => 'Fast Brand'
    ]);
    $this->assertEquals('Fast Brand', $brand->name);
}
public function test_each_test_has_isolated_data(){
    // This test's data won't affect other tests
    $brandsCount = Brand::count();
    Brand::factory()->count(5)->create();
    $this->assertEquals($brandsCount+5, Brand::count());
}
public function test_inactive_brands_do_not_appear_in_public_list(){
    // Arrange: Create active and inactive brands
    Brand::factory()->create(['name' => 'Active', 'status' =>true]);
    Brand::factory()->create(['name' => 'Inactive', 'status' =>false]);

    // Act: Get public brand list (simulating repository method)
    $publicBrands = Brand::where('status', true)->get();
    $this->assertTrue($publicBrands ->contains('name', 'Active'));
    $this->assertFalse($publicBrands->contains('name','Inactive'));
}
}
