<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Rafael Aguilar',
            'username' => 'aguilraf',
            'email' => 'aguilraf@capa.gob.mx',
            'password' => Hash::make('password'),
        ]);
    }
}
