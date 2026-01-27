<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'articulo' => fake()->word(),
            'cantidad' => fake()->numberBetween(1, 100),
            'es_default' => false,
            'unidad_medida_id' => null, // Optional by default
        ];
    }
}
