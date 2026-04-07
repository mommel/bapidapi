<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReservationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Reservation;

class ReservationService
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository,
        private readonly ParkingLotService $parkingLotService,
    ) {}

    public function list(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->reservationRepository->paginate($perPage, $filters);
    }

    public function findById(string $id): ?Reservation
    {
        return $this->reservationRepository->findById($id);
    }

    /**
     * Create a reservation after checking availability.
     *
     * @return array{reservation: Reservation|null, error: string|null}
     */
    public function create(array $data): array
    {
        // Check availability
        $availability = $this->parkingLotService->getAvailability(
            $data['parking_lot_id'],
            $data['check_in'],
            $data['check_out'],
        );

        if (!$availability || !$availability['available']) {
            return [
                'reservation' => null,
                'error' => 'Requested slot is no longer available',
            ];
        }

        // Generate reservation number and access code
        $data['reservation_number'] = 'R-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        $data['access_code'] = (string) random_int(100000, 999999);
        $data['status'] = 'confirmed';

        $reservation = $this->reservationRepository->create($data);

        return [
            'reservation' => $reservation,
            'error' => null,
        ];
    }

    /**
     * Cancel a reservation.
     *
     * @return array{success: bool, error: string|null}
     */
    public function cancel(string $id, ?string $reason = null): array
    {
        $reservation = $this->reservationRepository->findById($id);

        if (!$reservation) {
            return ['success' => false, 'error' => 'Reservation not found'];
        }

        $nonCancellable = ['checked_in', 'checked_out', 'cancelled'];
        if (in_array($reservation->status, $nonCancellable, true)) {
            return ['success' => false, 'error' => 'Reservation cannot be cancelled anymore'];
        }

        $this->reservationRepository->update($id, [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return ['success' => true, 'error' => null];
    }
}
