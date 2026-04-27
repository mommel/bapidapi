<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Driver;
use App\Models\ParkingLot;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $checkOut = (clone $checkIn)->modify('+'.$this->faker->numberBetween(8, 24).' hours');

        return [
            'reservation_number' => 'R-'.now()->format('Ymd').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'parking_lot_id' => ParkingLot::factory(),
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'status' => $this->faker->randomElement(['pending', 'confirmed']),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'access_code' => (string) $this->faker->numberBetween(100000, 999999),
            'total_price_amount' => $this->faker->randomFloat(2, 15, 80),
            'total_price_currency' => 'EUR',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
