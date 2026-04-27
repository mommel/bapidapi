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

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverService $driverService,
    ) {}

    /**
     * @OA\Get(
     *     path="/drivers",
     *     operationId="driverIndex",
     *     tags={"Drivers"},
     *     summary="List all drivers",
     *     description="Returns a paginated list of drivers. Supports optional full-text search via `q`.",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pageSize", in="query", required=false,
     *         description="Items per page (default 20)",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Parameter(
     *         name="q", in="query", required=false,
     *         description="Full-text search query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated driver list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/DriverResource")
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
        $drivers = $this->driverService->list(
            perPage: (int) $request->query('pageSize', '20'),
            search: $request->query('q'),
        );

        return DriverResource::collection($drivers);
    }

    /**
     * @OA\Post(
     *     path="/drivers",
     *     operationId="driverStore",
     *     tags={"Drivers"},
     *     summary="Create a new driver",
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"employeeNumber","firstName","lastName"},
     *             @OA\Property(property="employeeNumber", type="string", example="EMP-001"),
     *             @OA\Property(property="firstName", type="string", example="Jan"),
     *             @OA\Property(property="lastName", type="string", example="Kowalski"),
     *             @OA\Property(property="phone", type="string", example="+48123456789"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="language", type="string", example="pl"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Driver created",
     *         @OA\JsonContent(ref="#/components/schemas/DriverResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function store(StoreDriverRequest $request): JsonResponse
    {
        $driver = $this->driverService->create($request->validated());

        return (new DriverResource($driver))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/drivers/{driverId}",
     *     operationId="driverShow",
     *     tags={"Drivers"},
     *     summary="Get a single driver",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="driverId", in="path", required=true,
     *         description="Driver UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver details",
     *         @OA\JsonContent(ref="#/components/schemas/DriverResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/drivers/{driverId}",
     *     operationId="driverUpdate",
     *     tags={"Drivers"},
     *     summary="Update a driver",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="driverId", in="path", required=true,
     *         description="Driver UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="firstName", type="string", example="Jan"),
     *             @OA\Property(property="lastName", type="string", example="Nowak"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="language", type="string"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver updated",
     *         @OA\JsonContent(ref="#/components/schemas/DriverResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
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
