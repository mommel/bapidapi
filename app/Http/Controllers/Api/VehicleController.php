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

class VehicleController extends Controller
{
    public function __construct(
        private readonly VehicleService $vehicleService,
    ) {}

    /**
     * @OA\Get(
     *     path="/vehicles",
     *     operationId="vehicleIndex",
     *     tags={"Vehicles"},
     *     summary="List all vehicles",
     *     description="Returns a paginated list of vehicles. Supports optional full-text search via `q`.",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="pageSize", in="query", required=false,
     *         description="Items per page (default 20)",
     *
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *
     *     @OA\Parameter(
     *         name="q", in="query", required=false,
     *         description="Full-text search query",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated vehicle list",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/VehicleResource")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $vehicles = $this->vehicleService->list(
            perPage: (int) $request->query('pageSize', '20'),
            search: $request->query('q'),
        );

        return VehicleResource::collection($vehicles);
    }

    /**
     * @OA\Post(
     *     path="/vehicles",
     *     operationId="vehicleStore",
     *     tags={"Vehicles"},
     *     summary="Create a new vehicle",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"fleetNumber","type","licensePlate"},
     *
     *             @OA\Property(property="fleetNumber", type="string", example="FL-042"),
     *             @OA\Property(property="type", type="string", example="truck"),
     *             @OA\Property(property="licensePlate", type="string", example="WA12345"),
     *             @OA\Property(property="trailerPlate", type="string"),
     *             @OA\Property(property="adr", type="boolean", example=false),
     *             @OA\Property(property="refrigerated", type="boolean", example=false),
     *             @OA\Property(property="heightCm", type="integer"),
     *             @OA\Property(property="lengthCm", type="integer"),
     *             @OA\Property(property="weightKg", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Vehicle created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());

        return (new VehicleResource($vehicle))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/vehicles/{vehicleId}",
     *     operationId="vehicleShow",
     *     tags={"Vehicles"},
     *     summary="Get a single vehicle",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="vehicleId", in="path", required=true,
     *         description="Vehicle UUID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vehicle details",
     *
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Vehicle not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/vehicles/{vehicleId}",
     *     operationId="vehicleUpdate",
     *     tags={"Vehicles"},
     *     summary="Update a vehicle",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="vehicleId", in="path", required=true,
     *         description="Vehicle UUID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="fleetNumber", type="string"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="licensePlate", type="string"),
     *             @OA\Property(property="trailerPlate", type="string"),
     *             @OA\Property(property="adr", type="boolean"),
     *             @OA\Property(property="refrigerated", type="boolean"),
     *             @OA\Property(property="heightCm", type="integer"),
     *             @OA\Property(property="lengthCm", type="integer"),
     *             @OA\Property(property="weightKg", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vehicle updated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Vehicle not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
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
