<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\ParkingLotController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
*/

// ── Public ───────────────────────────────────────────────────────────────

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'OK',
        'data' => [
            'status' => 'ok',
        ],
        'meta' => [
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// ── Authenticated ────────────────────────────────────────────────────────

Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Parking Lots
    Route::get('/parking-lots', [ParkingLotController::class, 'index']);
    Route::get('/parking-lots/{parkingLotId}', [ParkingLotController::class, 'show']);
    Route::get('/parking-lots/{parkingLotId}/availability', [ParkingLotController::class, 'availability']);

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{reservationId}', [ReservationController::class, 'show']);
    Route::delete('/reservations/{reservationId}', [ReservationController::class, 'destroy']);

    // Drivers
    Route::get('/drivers', [DriverController::class, 'index']);
    Route::post('/drivers', [DriverController::class, 'store']);
    Route::get('/drivers/{driverId}', [DriverController::class, 'show']);
    Route::patch('/drivers/{driverId}', [DriverController::class, 'update']);

    // Vehicles
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles/{vehicleId}', [VehicleController::class, 'show']);
    Route::patch('/vehicles/{vehicleId}', [VehicleController::class, 'update']);
});
