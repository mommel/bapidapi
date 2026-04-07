<?php

declare(strict_types=1);

use App\Models\Driver;
use App\Models\ParkingLot;
use App\Models\Reservation;
use App\Models\Vehicle;

describe('GET /api/v1/reservations', function () {
    it('returns 401 without authentication', function () {
        $response = $this->getJson('/api/v1/reservations');

        $response->assertStatus(401);
    });

    it('returns a paginated list of reservations', function () {
        $token = authToken();
        Reservation::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/reservations', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'reservationNumber', 'status', 'checkIn', 'checkOut']],
            ]);
    });
});

describe('POST /api/v1/reservations', function () {
    it('returns 401 without authentication', function () {
        $response = $this->postJson('/api/v1/reservations', []);

        $response->assertStatus(401);
    });

    it('creates a reservation with valid data', function () {
        $token = authToken();
        $lot = ParkingLot::factory()->create([
            'capacity' => ['totalSpaces' => 50, 'availableSpaces' => 50],
        ]);
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $checkIn = now()->addDays(rand(1, 10))->toISOString();
        $checkOut = now()->addDays(rand(11, 20))->toISOString();

        $response = $this->postJson('/api/v1/reservations', [
            'parkingLotId' => $lot->id,
            'driverId' => $driver->id,
            'vehicleId' => $vehicle->id,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'notes' => fake()->optional()->sentence(),
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'reservationNumber', 'accessCode', 'status'],
            ])
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.parkingLotId', $lot->id)
            ->assertJsonPath('data.driverId', $driver->id)
            ->assertJsonPath('data.vehicleId', $vehicle->id);

        $this->assertDatabaseHas('reservations', [
            'parking_lot_id' => $lot->id,
            'driver_id' => $driver->id,
            'status' => 'confirmed',
        ]);
    });

    it('returns 422 when parkingLotId is missing', function () {
        $token = authToken();

        $response = $this->postJson('/api/v1/reservations', [
            'checkIn' => now()->addDays(1)->toISOString(),
            'checkOut' => now()->addDays(2)->toISOString(),
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422);
    });

    it('returns 422 when checkOut is before checkIn', function () {
        $token = authToken();
        $lot = ParkingLot::factory()->create();
        $driver = Driver::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->postJson('/api/v1/reservations', [
            'parkingLotId' => $lot->id,
            'driverId' => $driver->id,
            'vehicleId' => $vehicle->id,
            'checkIn' => now()->addDays(5)->toISOString(),
            'checkOut' => now()->addDays(2)->toISOString(),
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422);
    });
});

describe('GET /api/v1/reservations/{reservationId}', function () {
    it('returns a reservation by ID', function () {
        $token = authToken();
        $reservation = Reservation::factory()->create();

        $response = $this->getJson("/api/v1/reservations/{$reservation->id}", [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $reservation->id)
            ->assertJsonPath('data.reservationNumber', $reservation->reservation_number);
    });

    it('returns 404 for a non-existent reservation', function () {
        $token = authToken();

        $response = $this->getJson('/api/v1/reservations/00000000-0000-0000-0000-000000000000', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404);
    });
});

describe('DELETE /api/v1/reservations/{reservationId}', function () {
    it('cancels a confirmed reservation', function () {
        $token = authToken();
        $reservation = Reservation::factory()->create(['status' => 'confirmed']);

        $response = $this->deleteJson("/api/v1/reservations/{$reservation->id}", [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    });

    it('returns 409 for an already checked-in reservation', function () {
        $token = authToken();
        $reservation = Reservation::factory()->create(['status' => 'checked_in']);

        $response = $this->deleteJson("/api/v1/reservations/{$reservation->id}", [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(409);
    });

    it('returns 404 for a non-existent reservation', function () {
        $token = authToken();

        $response = $this->deleteJson('/api/v1/reservations/00000000-0000-0000-0000-000000000000', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404);
    });
});
