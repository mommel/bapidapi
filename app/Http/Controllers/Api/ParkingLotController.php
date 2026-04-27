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

    /**
     * @OA\Get(
     *     path="/parking-lots",
     *     operationId="parkingLotIndex",
     *     tags={"Parking Lots"},
     *     summary="List parking lots",
     *     description="Returns a paginated list of parking lots. Supports filtering by location and amenities.",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pageSize", in="query", required=false,
     *         description="Items per page (default 20)",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Parameter(
     *         name="countryCode", in="query", required=false,
     *         @OA\Schema(type="string", example="PL")
     *     ),
     *     @OA\Parameter(
     *         name="city", in="query", required=false,
     *         @OA\Schema(type="string", example="Warsaw")
     *     ),
     *     @OA\Parameter(
     *         name="latitude", in="query", required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="longitude", in="query", required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="radiusKm", in="query", required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="minSecurityLevel", in="query", required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="amenities", in="query", required=false,
     *         description="Comma-separated list of amenities",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated parking lot list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/ParkingLotResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/parking-lots/{parkingLotId}",
     *     operationId="parkingLotShow",
     *     tags={"Parking Lots"},
     *     summary="Get a single parking lot",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="parkingLotId", in="path", required=true,
     *         description="Parking lot UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parking lot details",
     *         @OA\JsonContent(ref="#/components/schemas/ParkingLotResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parking lot not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/parking-lots/{parkingLotId}/availability",
     *     operationId="parkingLotAvailability",
     *     tags={"Parking Lots"},
     *     summary="Check availability of a parking lot",
     *     description="Returns available spot counts for the requested check-in / check-out window.",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="parkingLotId", in="path", required=true,
     *         description="Parking lot UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="checkIn", in="query", required=true,
     *         description="Check-in datetime (ISO 8601)",
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Parameter(
     *         name="checkOut", in="query", required=true,
     *         description="Check-out datetime (ISO 8601), must be after checkIn",
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Parameter(
     *         name="vehicleType", in="query", required=false,
     *         description="Filter by vehicle type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability result",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parking lot not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
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
