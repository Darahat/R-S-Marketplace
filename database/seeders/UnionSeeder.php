<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UnionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all upazilas first
        $upazilas = DB::table('upazilas')->get();

        foreach ($upazilas as $upazila) {
            $response = Http::get("https://bdapi.vercel.app/api/v.1/union/{$upazila->id}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] == 200 && isset($data['data'])) {
                    $unions = $data['data'];

                    if (is_array($unions)) {
                        foreach ($unions as $union) {
                            DB::table('unions')->updateOrInsert(
                                ['id' => $union['id']],
                                [
                                    'id' => $union['id'],
                                    'upazila_id' => $upazila->id,
                                    'name' => $union['name'],
                                    'bn_name' => $union['bn_name'] ?? null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }
                    }
                }
            }

            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }        $this->command->info('Unions seeded successfully!');
    }
}