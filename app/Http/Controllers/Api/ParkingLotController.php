<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParkingLotResource;
use App\Services\ParkingLotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ParkingLotController extends Controller
{
    public function __construct(
        private readonly ParkingLotService $parkingLotService,
    ) {}

    #[OA\Get(
        path: '/parking-lots',
        operationId: 'parkingLotIndex',
        summary: 'List parking lots',
        description: 'Returns a paginated list of parking lots. Supports filtering by location and amenities.',
        security: [['BearerAuth' => []]],
        tags: ['Parking Lots'],
        parameters: [
            new OA\Parameter(
                name: 'pageSize',
                in: 'query',
                required: false,
                description: 'Items per page (default 20)',
                schema: new OA\Schema(type: 'integer', example: 20)
            ),
            new OA\Parameter(
                name: 'countryCode',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'PL')
            ),
            new OA\Parameter(
                name: 'city',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Warsaw')
            ),
            new OA\Parameter(
                name: 'latitude',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float')
            ),
            new OA\Parameter(
                name: 'longitude',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float')
            ),
            new OA\Parameter(
                name: 'radiusKm',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float')
            ),
            new OA\Parameter(
                name: 'minSecurityLevel',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'amenities',
                in: 'query',
                required: false,
                description: 'Comma-separated list of amenities',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated parking lot list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ParkingLotResource')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/parking-lots/{parkingLotId}',
        operationId: 'parkingLotShow',
        summary: 'Get a single parking lot',
        security: [['BearerAuth' => []]],
        tags: ['Parking Lots'],
        parameters: [
            new OA\Parameter(
                name: 'parkingLotId',
                in: 'path',
                required: true,
                description: 'Parking lot UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Parking lot details',
                content: new OA\JsonContent(ref: '#/components/schemas/ParkingLotResource')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Parking lot not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(string $parkingLotId): JsonResponse
    {
        $lot = $this->parkingLotService->findById($parkingLotId);

        if (! $lot) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Parking lot not found',
                ],
            ], 404);
        }

        return (new ParkingLotResource($lot))->response();
    }

    #[OA\Get(
        path: '/parking-lots/{parkingLotId}/availability',
        operationId: 'parkingLotAvailability',
        summary: 'Check availability of a parking lot',
        description: 'Returns available spot counts for the requested check-in / check-out window.',
        security: [['BearerAuth' => []]],
        tags: ['Parking Lots'],
        parameters: [
            new OA\Parameter(
                name: 'parkingLotId',
                in: 'path',
                required: true,
                description: 'Parking lot UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'checkIn',
                in: 'query',
                required: true,
                description: 'Check-in datetime (ISO 8601)',
                schema: new OA\Schema(type: 'string', format: 'date-time')
            ),
            new OA\Parameter(
                name: 'checkOut',
                in: 'query',
                required: true,
                description: 'Check-out datetime (ISO 8601), must be after checkIn',
                schema: new OA\Schema(type: 'string', format: 'date-time')
            ),
            new OA\Parameter(
                name: 'vehicleType',
                in: 'query',
                required: false,
                description: 'Filter by vehicle type',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Availability result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Parking lot not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
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
