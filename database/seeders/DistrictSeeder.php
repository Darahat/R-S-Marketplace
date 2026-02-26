<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DistrictSeeder extends Seeder
{
    use ColumnSafeSeeder;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $response = Http::get('https://bdapi.vercel.app/api/v.1/district');

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['status']) && $data['status'] == 200 && isset($data['data'])) {
                $districts = $data['data'];

                foreach ($districts as $district) {
                    $row = $this->filterRowByTable('districts', [
                        'id' => $district['id'],
                        'name' => $district['name'],
                        'bn_name' => $district['bn_name'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('districts')->updateOrInsert(
                        ['id' => $district['id']],
                        $row
                    );
                }

                $this->command->info('Districts seeded successfully!');
            }
        } else {
            $this->command->error('Failed to fetch districts from API');
        }
    }
}
