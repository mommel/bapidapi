<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'status', 'driverId', 'vehicleId', 'parkingLotId', 'from', 'to',
        ]);

        $reservations = $this->reservationService->list(
            perPage: (int) $request->query('pageSize', '20'),
            filters: $filters,
        );

        return ReservationResource::collection($reservations);
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $result = $this->reservationService->create($request->validated());

        if ($result['error']) {
            return response()->json([
                'error' => [
                    'code' => 'CONFLICT',
                    'message' => $result['error'],
                ],
            ], 409);
        }

        return (new ReservationResource($result['reservation']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $reservationId): JsonResponse
    {
        $reservation = $this->reservationService->findById($reservationId);

        if (!$reservation) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Reservation not found',
                ],
            ], 404);
        }

        return (new ReservationResource($reservation))->response();
    }

    public function destroy(string $reservationId): JsonResponse
    {
        $result = $this->reservationService->cancel($reservationId);

        if (!$result['success'] && $result['error'] === 'Reservation not found') {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => $result['error'],
                ],
            ], 404);
        }

        if (!$result['success']) {
            return response()->json([
                'error' => [
                    'code' => 'CONFLICT',
                    'message' => $result['error'],
                ],
            ], 409);
        }

        return response()->json(null, 204);
    }
}
