<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clave' => fake()->unique()->numerify('EMP###'),
            'nombre' => fake()->name(),
            'puesto' => fake()->jobTitle(),
            'departamento' => fake()->word(),
            'rfc' => fake()->unique()->bothify('????######???'),
            'categoria' => 'A',
            'fecha_alta' => now(),
            'nivel' => '1',
            'salario_diario' => fake()->randomFloat(2, 100, 1000),
            'salario_mensual' => fake()->randomFloat(2, 3000, 30000),
            'curp' => fake()->unique()->bothify('????######??????##'),
            'nss' => fake()->unique()->numerify('###########'),
            'afiliacion' => fake()->numerify('#####'),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->phoneNumber(),

            'activo' => true,
            'es_gerente' => false,
        ];
    }
}
