<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParkingLotResource;
use App\Services\ParkingLotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ParkingLotController extends Controller
{
    public function __construct(
        private readonly ParkingLotService $parkingLotService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'countryCode', 'city', 'latitude', 'longitude',
            'radiusKm', 'minSecurityLevel', 'amenities',
        ]);

        $lots = $this->parkingLotService->list(
            perPage: (int) $request->query('pageSize', '20'),
            filters: $filters,
        );

        return ParkingLotResource::collection($lots);
    }

    public function show(string $parkingLotId): JsonResponse
    {
        $lot = $this->parkingLotService->findById($parkingLotId);

        if (!$lot) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Parking lot not found',
                ],
            ], 404);
        }

        return (new ParkingLotResource($lot))->response();
    }

    public function availability(Request $request, string $parkingLotId): JsonResponse
    {
        $request->validate([
            'checkIn' => 'required|date',
            'checkOut' => 'required|date|after:checkIn',
            'vehicleType' => 'nullable|string',
        ]);

        $result = $this->parkingLotService->getAvailability(
            $parkingLotId,
            $request->query('checkIn'),
            $request->query('checkOut'),
            $request->query('vehicleType'),
        );

        if ($result === null) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Parking lot not found',
                ],
            ], 404);
        }

        return response()->json(['data' => $result]);
    }
}
