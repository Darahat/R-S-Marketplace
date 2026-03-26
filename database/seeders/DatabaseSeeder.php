<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use ColumnSafeSeeder;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Create Admin User
        $admin = $this->filterRowByTable('users', [
            'name' => 'Admin User',
            'email' => 'admin@marketplace.com',
            'password' => Hash::make('admin123'),
            'mobile' => '01700000000',
            'user_type' => 'ADMIN',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->updateOrInsert(['email' => 'admin@marketplace.com'], $admin);

        // Create Test Customer
        $customer = $this->filterRowByTable('users', [
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('customer123'),
            'mobile' => '01711111111',
            'user_type' => 'CUSTOMER',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->updateOrInsert(['email' => 'customer@example.com'], $customer);

        // Seed location data
        $this->call(DistrictSeeder::class);
        $this->call(UpazilaSeeder::class);
        $this->call(UnionSeeder::class);

        // Seed categories
        $this->call(CategorySeeder::class);

        // Seed brands
        $this->call(BrandSeeder::class);

        // Seed products
        $this->call(ProductSeeder::class);

        // Seed reviews
        $this->call(ReviewSeeder::class);

        // Seed hero section
        $this->call(HeroSectionSeeder::class);

        // Seed customer addresses
        $this->call(UserAddressSeeder::class);
    }
}
