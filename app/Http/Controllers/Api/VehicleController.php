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

    public function index(Request $request): AnonymousResourceCollection
    {
        $vehicles = $this->vehicleService->list(
            perPage: (int) $request->query('pageSize', '20'),
            search: $request->query('q'),
        );

        return VehicleResource::collection($vehicles);
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());

        return (new VehicleResource($vehicle))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $vehicleId): JsonResponse
    {
        $vehicle = $this->vehicleService->findById($vehicleId);

        if (!$vehicle) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Vehicle not found',
                ],
            ], 404);
        }

        return (new VehicleResource($vehicle))->response();
    }

    public function update(UpdateVehicleRequest $request, string $vehicleId): JsonResponse
    {
        $vehicle = $this->vehicleService->update($vehicleId, $request->validated());

        if (!$vehicle) {
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
