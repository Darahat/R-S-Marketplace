<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpazilaSeeder extends Seeder
{
    use ColumnSafeSeeder;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all districts first
        $districts = DB::table('districts')->get();

        foreach ($districts as $district) {
            $response = Http::get("https://bdapi.vercel.app/api/v.1/upazilla/{$district->id}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] == 200 && isset($data['data'])) {
                    $upazilas = $data['data'];

                    if (is_array($upazilas)) {
                        foreach ($upazilas as $upazila) {
                            $row = $this->filterRowByTable('upazilas', [
                                'id' => $upazila['id'],
                                'district_id' => $district->id,
                                'name' => $upazila['name'],
                                'bn_name' => $upazila['bn_name'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            DB::table('upazilas')->updateOrInsert(
                                ['id' => $upazila['id']],
                                $row
                            );
                        }
                    }
                }
            }

            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }

        $this->command->info('Upazilas seeded successfully!');
    }
}
