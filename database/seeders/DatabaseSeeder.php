<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@marketplace.com',
            'password' => Hash::make('admin123'),
            'mobile' => '01700000000',
            'user_type' => 'ADMIN',
        ]);

        // Create Test Customer
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('customer123'),
            'mobile' => '01711111111',
            'user_type' => 'CUSTOMER',
        ]);

        // Seed location data
        $this->call(DistrictSeeder::class);
        $this->call(UpazilaSeeder::class);
        $this->call(UnionSeeder::class);

        // Seed categories
        $this->call(CategorySeeder::class);

        // Seed products
        $this->call(ProductSeeder::class);

        // Seed reviews
        $this->call(ReviewSeeder::class);

        // Seed hero section
        $this->call(HeroSectionSeeder::class);
    }
}