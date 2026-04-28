<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Http\Resources\DriverResource;
use App\Services\DriverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverService $driverService,
    ) {}

    #[OA\Get(
        path: '/drivers',
        operationId: 'driverIndex',
        summary: 'List all drivers',
        description: 'Returns a paginated list of drivers. Supports optional full-text search via `q`.',
        security: [['BearerAuth' => []]],
        tags: ['Drivers'],
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
                description: 'Paginated driver list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/DriverResource')
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
        $drivers = $this->driverService->list(
            perPage: (int) $request->query('pageSize', '20'),
            search: $request->query('q'),
        );

        return DriverResource::collection($drivers);
    }

    #[OA\Post(
        path: '/drivers',
        operationId: 'driverStore',
        summary: 'Create a new driver',
        security: [['BearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['employeeNumber', 'firstName', 'lastName'],
                properties: [
                    new OA\Property(property: 'employeeNumber', type: 'string', example: 'EMP-001'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Kowalski'),
                    new OA\Property(property: 'phone', type: 'string', example: '+48123456789'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'language', type: 'string', example: 'pl'),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        tags: ['Drivers'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Driver created',
                content: new OA\JsonContent(ref: '#/components/schemas/DriverResource')
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
    public function store(StoreDriverRequest $request): JsonResponse
    {
        $driver = $this->driverService->create($request->validated());

        return (new DriverResource($driver))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/drivers/{driverId}',
        operationId: 'driverShow',
        summary: 'Get a single driver',
        security: [['BearerAuth' => []]],
        tags: ['Drivers'],
        parameters: [
            new OA\Parameter(
                name: 'driverId',
                in: 'path',
                required: true,
                description: 'Driver UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Driver details',
                content: new OA\JsonContent(ref: '#/components/schemas/DriverResource')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Driver not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(string $driverId): JsonResponse
    {
        $driver = $this->driverService->findById($driverId);

        if (! $driver) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Driver not found',
                ],
            ], 404);
        }

        return (new DriverResource($driver))->response();
    }

    #[OA\Patch(
        path: '/drivers/{driverId}',
        operationId: 'driverUpdate',
        summary: 'Update a driver',
        security: [['BearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'Jan'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Nowak'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'language', type: 'string'),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        tags: ['Drivers'],
        parameters: [
            new OA\Parameter(
                name: 'driverId',
                in: 'path',
                required: true,
                description: 'Driver UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Driver updated',
                content: new OA\JsonContent(ref: '#/components/schemas/DriverResource')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Driver not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function update(UpdateDriverRequest $request, string $driverId): JsonResponse
    {
        $driver = $this->driverService->update($driverId, $request->validated());

        if (! $driver) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Driver not found',
                ],
            ], 404);
        }

        return (new DriverResource($driver))->response();
    }
}
