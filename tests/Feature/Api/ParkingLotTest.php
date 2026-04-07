<?php

declare(strict_types=1);

use App\Models\ParkingLot;

describe('GET /api/v1/parking-lots', function () {
    it('returns 401 without authentication', function () {
        $response = $this->getJson('/api/v1/parking-lots');

        $response->assertStatus(401);
    });

    it('returns a paginated list of parking lots', function () {
        $token = authToken();
        ParkingLot::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/parking-lots', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'address', 'coordinates', 'securityLevel']],
            ]);
    });
});

describe('GET /api/v1/parking-lots/{parkingLotId}', function () {
    it('returns a parking lot by ID', function () {
        $token = authToken();
        $lot = ParkingLot::factory()->create();

        $response = $this->getJson("/api/v1/parking-lots/{$lot->id}", [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $lot->id);
    });

    it('returns 404 for a non-existent parking lot', function () {
        $token = authToken();

        $response = $this->getJson('/api/v1/parking-lots/00000000-0000-0000-0000-000000000000', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404);
    });
});

describe('GET /api/v1/parking-lots/{parkingLotId}/availability', function () {
    it('returns availability for a parking lot', function () {
        $token = authToken();
        $lot = ParkingLot::factory()->create([
            'capacity' => ['totalSpaces' => 50, 'availableSpaces' => 50],
        ]);

        $checkIn = now()->addDays(1)->toISOString();
        $checkOut = now()->addDays(2)->toISOString();

        $response = $this->getJson("/api/v1/parking-lots/{$lot->id}/availability?checkIn={$checkIn}&checkOut={$checkOut}", [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.available', true)
            ->assertJsonPath('data.parkingLotId', $lot->id);
    });

    it('returns 404 for a non-existent parking lot', function () {
        $token = authToken();

        $checkIn = now()->addDays(1)->toISOString();
        $checkOut = now()->addDays(2)->toISOString();

        $response = $this->getJson("/api/v1/parking-lots/00000000-0000-0000-0000-000000000000/availability?checkIn={$checkIn}&checkOut={$checkOut}", [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404);
    });
});
