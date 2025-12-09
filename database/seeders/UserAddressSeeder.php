<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserAddressSeeder extends Seeder
{
    public function run()
    {
        DB::table('addresses')->insert([
            [
                'user_id' => 1,
                'address_type' => 'billing',
                'full_name' => 'John Doe',
                'phone' => '01700000001',
                'street_address' => '123 Main Street',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'postal_code' => '1207',
                'country' => 'Bangladesh',
                'is_default' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'address_type' => 'shipping',
                'full_name' => 'John Doe',
                'phone' => '01700000002',
                'street_address' => '456 Office Road',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'postal_code' => '1212',
                'country' => 'Bangladesh',
                'is_default' => false,
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'address_type' => 'billing',
                'full_name' => 'John Doe',
                'phone' => '01700000003',
                'street_address' => '789 Vacation Ave',
                'city' => 'Chittagong',
                'state' => 'Chittagong',
                'postal_code' => '4000',
                'country' => 'Bangladesh',
                'is_default' => false,
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}