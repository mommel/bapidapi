<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class VehicleController extends Controller
{
    public function __construct(
        private readonly VehicleService $vehicleService,
    ) {}

    #[OA\Get(
        path: '/vehicles',
        operationId: 'vehicleIndex',
        summary: 'List all vehicles',
        description: 'Returns a paginated list of vehicles. Supports optional full-text search via `q`.',
        security: [['BearerAuth' => []]],
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(
                name: 'pageSize',
                in: 'query',
                required: false,
                description: 'Items per page (default 20)',
                schema: new OA\Schema(type: 'integer', example: 20)
            ),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: false,
                description: 'Full-text search query',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated vehicle list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/VehicleResource')
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
        $vehicles = $this->vehicleService->list(
            perPage: (int) $request->query('pageSize', '20'),
            search: $request->query('q'),
        );

        return VehicleResource::collection($vehicles);
    }

    #[OA\Post(
        path: '/vehicles',
        operationId: 'vehicleStore',
        summary: 'Create a new vehicle',
        security: [['BearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['fleetNumber', 'type', 'licensePlate'],
                properties: [
                    new OA\Property(property: 'fleetNumber', type: 'string', example: 'FL-042'),
                    new OA\Property(property: 'type', type: 'string', example: 'truck'),
                    new OA\Property(property: 'licensePlate', type: 'string', example: 'WA12345'),
                    new OA\Property(property: 'trailerPlate', type: 'string'),
                    new OA\Property(property: 'adr', type: 'boolean', example: false),
                    new OA\Property(property: 'refrigerated', type: 'boolean', example: false),
                    new OA\Property(property: 'heightCm', type: 'integer'),
                    new OA\Property(property: 'lengthCm', type: 'integer'),
                    new OA\Property(property: 'weightKg', type: 'integer'),
                ]
            )
        ),
        tags: ['Vehicles'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Vehicle created',
                content: new OA\JsonContent(ref: '#/components/schemas/VehicleResource')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());

        return (new VehicleResource($vehicle))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/vehicles/{vehicleId}',
        operationId: 'vehicleShow',
        summary: 'Get a single vehicle',
        security: [['BearerAuth' => []]],
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(
                name: 'vehicleId',
                in: 'path',
                required: true,
                description: 'Vehicle UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Vehicle details',
                content: new OA\JsonContent(ref: '#/components/schemas/VehicleResource')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Vehicle not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(string $vehicleId): JsonResponse
    {
        $vehicle = $this->vehicleService->findById($vehicleId);

        if (! $vehicle) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Vehicle not found',
                ],
            ], 404);
        }

        return (new VehicleResource($vehicle))->response();
    }

    #[OA\Patch(
        path: '/vehicles/{vehicleId}',
        operationId: 'vehicleUpdate',
        summary: 'Update a vehicle',
        security: [['BearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'fleetNumber', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'licensePlate', type: 'string'),
                    new OA\Property(property: 'trailerPlate', type: 'string'),
                    new OA\Property(property: 'adr', type: 'boolean'),
                    new OA\Property(property: 'refrigerated', type: 'boolean'),
                    new OA\Property(property: 'heightCm', type: 'integer'),
                    new OA\Property(property: 'lengthCm', type: 'integer'),
                    new OA\Property(property: 'weightKg', type: 'integer'),
                ]
            )
        ),
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(
                name: 'vehicleId',
                in: 'path',
                required: true,
                description: 'Vehicle UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Vehicle updated',
                content: new OA\JsonContent(ref: '#/components/schemas/VehicleResource')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Vehicle not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function update(UpdateVehicleRequest $request, string $vehicleId): JsonResponse
    {
        $vehicle = $this->vehicleService->update($vehicleId, $request->validated());

        if (! $vehicle) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Vehicle not found',
                ],
            ], 404);
        }

        return (new VehicleResource($vehicle))->response();
    }
}
