<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserAddressSeeder extends Seeder
{
    use ColumnSafeSeeder;

    public function run(): void
    {
        $now = Carbon::now();

        $customers = User::query()
            ->where('user_type', 'CUSTOMER')
            ->select('id', 'name', 'email', 'mobile')
            ->get();

        if ($customers->isEmpty()) {
            return;
        }

        $rows = [];

        foreach ($customers as $customer) {
            $rows[] = [
                'user_id' => $customer->id,
                'address_type' => 'shipping',
                'full_name' => $customer->name,
                'phone' => $customer->mobile,
                'email' => $customer->email,
                'district_id' => null,
                'upazila_id' => null,
                'union_id' => null,
                'street_address' => 'House 10, Road 5',
                'postal_code' => '1207',
                'country' => 'Bangladesh',
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rows[] = [
                'user_id' => $customer->id,
                'address_type' => 'billing',
                'full_name' => $customer->name,
                'phone' => $customer->mobile,
                'email' => $customer->email,
                'district_id' => null,
                'upazila_id' => null,
                'union_id' => null,
                'street_address' => 'Office 2B, Avenue 1',
                'postal_code' => '1212',
                'country' => 'Bangladesh',
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $safeRows = $this->filterRowsByTable('addresses', $rows);

        foreach ($safeRows as $row) {
            DB::table('addresses')->updateOrInsert(
                [
                    'user_id' => $row['user_id'],
                    'address_type' => $row['address_type'],
                    'street_address' => $row['street_address'],
                ],
                $row
            );
        }
    }
}
