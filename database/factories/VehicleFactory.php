<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fleet_number' => 'FL-' . $this->faker->unique()->numerify('####'),
            'type' => $this->faker->randomElement(['truck', 'truck_trailer', 'van', 'adr_truck', 'refrigerated_truck']),
            'license_plate' => strtoupper($this->faker->bothify('??-??-####')),
            'trailer_plate' => $this->faker->optional(0.5)->bothify('??-??-####'),
            'adr' => $this->faker->boolean(20),
            'refrigerated' => $this->faker->boolean(15),
            'height_cm' => $this->faker->numberBetween(250, 450),
            'length_cm' => $this->faker->numberBetween(600, 1800),
            'weight_kg' => $this->faker->numberBetween(3500, 44000),
        ];
    }
}
