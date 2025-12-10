<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DistrictSeeder extends Seeder
{
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
                    DB::table('districts')->updateOrInsert(
                        ['id' => $district['id']],
                        [
                            'id' => $district['id'],
                            'name' => $district['name'],
                            'bn_name' => $district['bn_name'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                $this->command->info('Districts seeded successfully!');
            }
        } else {
            $this->command->error('Failed to fetch districts from API');
        }
    }
}
