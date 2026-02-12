<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FirefightersModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Firefighter Settings (Key-Value structure)
        $settings = [
            ['key' => 'subtotal_percentage', 'value' => '0.90'],
            ['key' => 'commission_percentage', 'value' => '0.10'],
            ['key' => 'current_year', 'value' => date('Y')],
        ];

        foreach ($settings as $setting) {
            DB::table('firefighter_settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Communities
        $communities = [
            ['name' => 'José María Morelos', 'geolocation' => '19.7456,-88.7056'],
            ['name' => 'Dziuché', 'geolocation' => '19.9072,-88.7567'],
            ['name' => 'Sabán', 'geolocation' => '20.0381,-88.5432'],
        ];

        foreach ($communities as $community) {
            DB::table('communities')->insertOrIgnore(array_merge($community, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // 3. Firefighters (One per community for testing)
        $communityIds = DB::table('communities')->pluck('id', 'name');

        $firefighters = [
            [
                'name' => 'Juan Pérez (JMM)',
                'community_id' => $communityIds['José María Morelos'] ?? 1,
                'active' => true,
                'contact_number' => '9991234567',
                'max_rounding_amount' => 500.00
            ],
            [
                'name' => 'Pedro López (Dziuché)',
                'community_id' => $communityIds['Dziuché'] ?? 1,
                'active' => true,
                'contact_number' => '9997654321',
                'max_rounding_amount' => 300.00
            ],
        ];

        foreach ($firefighters as $ff) {
            // Check if exists to avoid duplicates on re-run
            $exists = DB::table('firefighters')->where('name', $ff['name'])->exists();
            if (!$exists) {
                DB::table('firefighters')->insert(array_merge($ff, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }
}
