<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Provider::updateOrCreate(
            ['rfc' => 'CSS160330CP7'],
            [
                'name' => 'CFE SUMINISTRADOR DE SERVICIOS',
                'bank_name' => 'BBVA MEXICO',
                'account_number' => '012914002014055000',
                'active' => true
            ]
        );
    }
}
