<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingLot>
 */
class ParkingLotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->countryCode() . '-' . $this->faker->citySuffix() . '-' . $this->faker->numerify('###')),
            'name' => 'Parking ' . $this->faker->city(),
            'description' => $this->faker->sentence(),
            'address_street' => $this->faker->streetAddress(),
            'address_postal_code' => $this->faker->postcode(),
            'address_city' => $this->faker->city(),
            'address_state' => $this->faker->state(),
            'address_country_code' => 'DE',
            'latitude' => $this->faker->latitude(47.0, 55.0),
            'longitude' => $this->faker->longitude(5.0, 15.0),
            'security_level' => $this->faker->randomElement(['basic', 'guarded', 'fenced', 'gated', 'cctv', 'secure']),
            'amenities' => $this->faker->randomElements(['shower', 'toilet', 'wifi', 'restaurant', 'fuel', 'electric_charging'], 3),
            'opening_hours' => ['is24x7' => true, 'timezone' => 'Europe/Berlin'],
            'capacity' => [
                'totalSpaces' => $this->faker->numberBetween(20, 200),
                'availableSpaces' => $this->faker->numberBetween(5, 50),
            ],
            'operator_name' => $this->faker->company(),
            'contact_phone' => $this->faker->phoneNumber(),
            'check_in_instructions' => 'Use gate terminal and reservation code on arrival.',
            'pricing' => [
                'basePrice' => ['amount' => $this->faker->randomFloat(2, 10, 50), 'currency' => 'EUR'],
                'taxesIncluded' => true,
                'priceUnit' => 'per_night',
            ],
        ];
    }
}
