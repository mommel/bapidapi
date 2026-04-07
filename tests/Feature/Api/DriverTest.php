<?php

declare(strict_types=1);

use App\Models\Driver;

describe('GET /api/v1/drivers', function () {
    it('returns 401 without authentication', function () {
        $response = $this->getJson('/api/v1/drivers');

        $response->assertStatus(401);
    });

    it('returns a paginated list of drivers', function () {
        $token = authToken();
        Driver::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/drivers', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'firstName', 'lastName']],
            ]);
    });

    it('supports search by name', function () {
        $token = authToken();
        $target = Driver::factory()->create(['first_name' => 'Xandro', 'last_name' => 'Zetterberg']);
        Driver::factory()->count(3)->create(); // noise

        $response = $this->getJson('/api/v1/drivers?q=Xandro', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $target->id);
    });
});

describe('POST /api/v1/drivers', function () {
    it('returns 401 without authentication', function () {
        $payload = Driver::factory()->make()->only(['first_name', 'last_name']);

        $response = $this->postJson('/api/v1/drivers', [
            'firstName' => $payload['first_name'],
            'lastName' => $payload['last_name'],
        ]);

        $response->assertStatus(401);
    });

    it('creates a driver with valid data', function () {
        $token = authToken();
        $fake = Driver::factory()->make();

        $response = $this->postJson('/api/v1/drivers', [
            'firstName' => $fake->first_name,
            'lastName' => $fake->last_name,
            'email' => $fake->email,
            'phone' => $fake->phone,
            'language' => $fake->language,
            'employeeNumber' => $fake->employee_number,
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.firstName', $fake->first_name)
            ->assertJsonPath('data.lastName', $fake->last_name)
            ->assertJsonPath('data.email', $fake->email);

        $this->assertDatabaseHas('drivers', [
            'first_name' => $fake->first_name,
            'last_name' => $fake->last_name,
        ]);
    });

    it('returns 422 when firstName is missing', function () {
        $token = authToken();
        $fake = Driver::factory()->make();

        $response = $this->postJson('/api/v1/drivers', [
            'lastName' => $fake->last_name,
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422);
    });
});

describe('GET /api/v1/drivers/{driverId}', function () {
    it('returns a driver by ID', function () {
        $token = authToken();
        $driver = Driver::factory()->create();

        $response = $this->getJson("/api/v1/drivers/{$driver->id}", [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $driver->id)
            ->assertJsonPath('data.firstName', $driver->first_name)
            ->assertJsonPath('data.lastName', $driver->last_name);
    });

    it('returns 404 for a non-existent driver', function () {
        $token = authToken();

        $response = $this->getJson('/api/v1/drivers/00000000-0000-0000-0000-000000000000', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404);
    });
});

describe('PATCH /api/v1/drivers/{driverId}', function () {
    it('updates a driver', function () {
        $token = authToken();
        $driver = Driver::factory()->create();
        $newData = Driver::factory()->make();

        $response = $this->patchJson("/api/v1/drivers/{$driver->id}", [
            'firstName' => $newData->first_name,
            'phone' => $newData->phone,
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.firstName', $newData->first_name)
            ->assertJsonPath('data.phone', $newData->phone);

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'first_name' => $newData->first_name,
        ]);
    });

    it('returns 404 for a non-existent driver', function () {
        $token = authToken();
        $fake = Driver::factory()->make();

        $response = $this->patchJson('/api/v1/drivers/00000000-0000-0000-0000-000000000000', [
            'firstName' => $fake->first_name,
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404);
    });
});
