<?php

declare(strict_types=1);

use App\Models\Vehicle;

describe('GET /api/v1/vehicles', function () {
    it('returns 401 without authentication', function () {
        $response = $this->getJson('/api/v1/vehicles');

        $response->assertStatus(401);
    });

    it('returns a paginated list of vehicles', function () {
        $token = authToken();
        Vehicle::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/vehicles', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'type', 'licensePlate']],
            ]);
    });
});

describe('POST /api/v1/vehicles', function () {
    it('returns 401 without authentication', function () {
        $fake = Vehicle::factory()->make();

        $response = $this->postJson('/api/v1/vehicles', [
            'type' => $fake->type,
            'licensePlate' => $fake->license_plate,
        ]);

        $response->assertStatus(401);
    });

    it('creates a vehicle with valid data', function () {
        $token = authToken();
        $fake = Vehicle::factory()->make();

        $response = $this->postJson('/api/v1/vehicles', [
            'type' => $fake->type,
            'licensePlate' => $fake->license_plate,
            'fleetNumber' => $fake->fleet_number,
            'trailerPlate' => $fake->trailer_plate,
            'adr' => $fake->adr,
            'refrigerated' => $fake->refrigerated,
            'heightCm' => $fake->height_cm,
            'lengthCm' => $fake->length_cm,
            'weightKg' => $fake->weight_kg,
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', $fake->type)
            ->assertJsonPath('data.licensePlate', $fake->license_plate);

        $this->assertDatabaseHas('vehicles', [
            'type' => $fake->type,
            'license_plate' => $fake->license_plate,
        ]);
    });

    it('returns 422 for invalid vehicle type', function () {
        $token = authToken();

        $response = $this->postJson('/api/v1/vehicles', [
            'type' => 'spaceship',
            'licensePlate' => 'GI-AB-1234',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422);
    });

    it('returns 422 when licensePlate is missing', function () {
        $token = authToken();

        $response = $this->postJson('/api/v1/vehicles', [
            'type' => 'truck',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422);
    });
});

describe('GET /api/v1/vehicles/{vehicleId}', function () {
    it('returns a vehicle by ID', function () {
        $token = authToken();
        $vehicle = Vehicle::factory()->create();

        $response = $this->getJson("/api/v1/vehicles/{$vehicle->id}", [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $vehicle->id)
            ->assertJsonPath('data.type', $vehicle->type)
            ->assertJsonPath('data.licensePlate', $vehicle->license_plate);
    });

    it('returns 404 for a non-existent vehicle', function () {
        $token = authToken();

        $response = $this->getJson('/api/v1/vehicles/00000000-0000-0000-0000-000000000000', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404);
    });
});

describe('PATCH /api/v1/vehicles/{vehicleId}', function () {
    it('updates a vehicle', function () {
        $token = authToken();
        $vehicle = Vehicle::factory()->create();
        $newData = Vehicle::factory()->make();

        $response = $this->patchJson("/api/v1/vehicles/{$vehicle->id}", [
            'licensePlate' => $newData->license_plate,
            'fleetNumber' => $newData->fleet_number,
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.licensePlate', $newData->license_plate);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'license_plate' => $newData->license_plate,
        ]);
    });
});
