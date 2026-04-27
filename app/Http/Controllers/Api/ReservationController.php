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

    /**
     * @OA\Get(
     *     path="/reservations",
     *     operationId="reservationIndex",
     *     tags={"Reservations"},
     *     summary="List reservations",
     *     description="Returns a paginated list of reservations. Supports filtering by status, driver, vehicle, parking lot, and date range.",
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
     *         name="status", in="query", required=false,
     *
     *         @OA\Schema(type="string", enum={"active","cancelled","completed"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="driverId", in="query", required=false,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Parameter(
     *         name="vehicleId", in="query", required=false,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Parameter(
     *         name="parkingLotId", in="query", required=false,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Parameter(
     *         name="from", in="query", required=false,
     *         description="Filter reservations from this date (ISO 8601)",
     *
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *
     *     @OA\Parameter(
     *         name="to", in="query", required=false,
     *         description="Filter reservations up to this date (ISO 8601)",
     *
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated reservation list",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/ReservationResource")
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
        $filters = $request->only([
            'status', 'driverId', 'vehicleId', 'parkingLotId', 'from', 'to',
        ]);

        $reservations = $this->reservationService->list(
            perPage: (int) $request->query('pageSize', '20'),
            filters: $filters,
        );

        return ReservationResource::collection($reservations);
    }

    /**
     * @OA\Post(
     *     path="/reservations",
     *     operationId="reservationStore",
     *     tags={"Reservations"},
     *     summary="Create a reservation",
     *     description="Creates a new parking reservation. Returns 409 if the slot is no longer available.",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"parkingLotId","driverId","vehicleId","checkIn","checkOut"},
     *
     *             @OA\Property(property="parkingLotId", type="string", format="uuid"),
     *             @OA\Property(property="driverId", type="string", format="uuid"),
     *             @OA\Property(property="vehicleId", type="string", format="uuid"),
     *             @OA\Property(property="checkIn", type="string", format="date-time"),
     *             @OA\Property(property="checkOut", type="string", format="date-time"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Reservation created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReservationResource")
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
     *         response=409,
     *         description="Conflict — slot unavailable",
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

    /**
     * @OA\Get(
     *     path="/reservations/{reservationId}",
     *     operationId="reservationShow",
     *     tags={"Reservations"},
     *     summary="Get a single reservation",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="reservationId", in="path", required=true,
     *         description="Reservation UUID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reservation details",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReservationResource")
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
     *         description="Reservation not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(string $reservationId): JsonResponse
    {
        $reservation = $this->reservationService->findById($reservationId);

        if (! $reservation) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Reservation not found',
                ],
            ], 404);
        }

        return (new ReservationResource($reservation))->response();
    }

    /**
     * @OA\Delete(
     *     path="/reservations/{reservationId}",
     *     operationId="reservationDestroy",
     *     tags={"Reservations"},
     *     summary="Cancel a reservation",
     *     description="Cancels an active reservation. Returns 409 if it cannot be cancelled (e.g. already completed).",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="reservationId", in="path", required=true,
     *         description="Reservation UUID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(response=204, description="Reservation cancelled"),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Reservation not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Cannot cancel reservation in its current state",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(string $reservationId): JsonResponse
    {
        $result = $this->reservationService->cancel($reservationId);

        if (! $result['success'] && $result['error'] === 'Reservation not found') {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => $result['error'],
                ],
            ], 404);
        }

        if (! $result['success']) {
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
