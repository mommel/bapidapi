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

    public function index(Request $request): AnonymousResourceCollection
    {
        $drivers = $this->driverService->list(
            perPage: (int) $request->query('pageSize', '20'),
            search: $request->query('q'),
        );

        return DriverResource::collection($drivers);
    }

    public function store(StoreDriverRequest $request): JsonResponse
    {
        $driver = $this->driverService->create($request->validated());

        return (new DriverResource($driver))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $driverId): JsonResponse
    {
        $driver = $this->driverService->findById($driverId);

        if (!$driver) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Driver not found',
                ],
            ], 404);
        }

        return (new DriverResource($driver))->response();
    }

    public function update(UpdateDriverRequest $request, string $driverId): JsonResponse
    {
        $driver = $this->driverService->update($driverId, $request->validated());

        if (!$driver) {
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
