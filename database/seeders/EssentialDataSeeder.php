<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EssentialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Capitulos (Presupuesto)
        $capitulos = [
            ['codigo' => '1000', 'descripcion' => 'SERVICIOS PERSONALES'],
            ['codigo' => '2000', 'descripcion' => 'MATERIALES Y SUMINISTROS'],
            ['codigo' => '3000', 'descripcion' => 'SERVICIOS GENERALES'],
            ['codigo' => '4000', 'descripcion' => 'TRANSFERENCIAS, ASIGNACIONES, SUBSIDIOS Y OTRAS AYUDAS'],
            ['codigo' => '5000', 'descripcion' => 'BIENES MUEBLES, INMUEBLES E INTANGIBLES'],
        ];

        foreach ($capitulos as $cap) {
            DB::table('capitulos')->insertOrIgnore(array_merge($cap, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // 2. Partidas (Presupuesto)
        $capituloIds = DB::table('capitulos')->pluck('id', 'codigo');

        $partidas = [
            // Materiales (2000)
            ['codigo' => '21101', 'descripcion' => 'MATERIALES, ÚTILES Y EQUIPOS MENORES DE OFICINA', 'capitulo_codigo' => '2000'],
            ['codigo' => '21601', 'descripcion' => 'MATERIAL DE LIMPIEZA', 'capitulo_codigo' => '2000'],
            ['codigo' => '26101', 'descripcion' => 'COMBUSTIBLES, LUBRICANTES Y ADITIVOS', 'capitulo_codigo' => '2000'],
            ['codigo' => '22104', 'descripcion' => 'PRODUCTOS ALIMENTICIOS PARA EL PERSONAL EN LAS INSTALACIONES', 'capitulo_codigo' => '2000'],

            // Servicios Generales (3000)
            ['codigo' => '37501', 'descripcion' => 'VIÁTICOS EN EL PAÍS', 'capitulo_codigo' => '3000'],
            ['codigo' => '37201', 'descripcion' => 'PASAJES TERRESTRES', 'capitulo_codigo' => '3000'],
            ['codigo' => '37901', 'descripcion' => 'OTROS SERVICIOS DE TRASLADO Y HOSPEDAJE', 'capitulo_codigo' => '3000'],
            ['codigo' => '35501', 'descripcion' => 'MANTENIMIENTO Y CONSERVACIÓN DE VEHÍCULOS', 'capitulo_codigo' => '3000'],
        ];

        foreach ($partidas as $part) {
            $capId = $capituloIds[$part['capitulo_codigo']] ?? null;
            if ($capId) {
                DB::table('partidas')->insertOrIgnore([
                    'capitulo_id' => $capId,
                    'codigo' => $part['codigo'],
                    'descripcion' => $part['descripcion'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 3. Unidades de Medida
        $unidades = ['PZA', 'KG', 'LTS', 'MTS', 'SERVICIO', 'CAJA', 'PAQUETE', 'JUEGO'];

        foreach ($unidades as $unidad) {
            DB::table('unidad_medidas')->insertOrIgnore([
                'nombre' => $unidad,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 4. Materiales (Ejemplos)
        $unidadIds = DB::table('unidad_medidas')->pluck('id', 'nombre');

        $materiales = [
            ['articulo' => 'GASOLINA MAGNA', 'unidad' => 'LTS'],
            ['articulo' => 'PAPEL BOND CARTA', 'unidad' => 'PAQUETE'],
            ['articulo' => 'BOLIGRAFO NEGRO', 'unidad' => 'PZA'],
            ['articulo' => 'CLORO', 'unidad' => 'LTS'],
        ];

        foreach ($materiales as $mat) {
            $unidadId = $unidadIds[$mat['unidad']] ?? null;

            if ($unidadId) {
                DB::table('materials')->insertOrIgnore([
                    'unidad_medida_id' => $unidadId,
                    'articulo' => $mat['articulo'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 5. Leyendas (Default)
        DB::table('leyendas')->insertOrIgnore([
            'user_id' => 1, // Primer usuario (admin)
            'anio' => date('Y'),
            'texto' => 'Elaboró: C. Rafael Aguilar - Administrador',
            'activa' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
