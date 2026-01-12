<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Configuracion::create([
            'nombre_empresa' => 'COMISION DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO',
            'nombre_organismo' => 'ORGANISMO OPERADOR JOSE MARIA MORELOS',
            'iva' => 16.00,
        ]);
    }
}
