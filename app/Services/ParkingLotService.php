<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ParkingLotRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\ParkingLot;

class ParkingLotService
{
    public function __construct(
        private readonly ParkingLotRepositoryInterface $parkingLotRepository,
    ) {}

    public function list(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->parkingLotRepository->paginate($perPage, $filters);
    }

    public function findById(string $id): ?ParkingLot
    {
        return $this->parkingLotRepository->findById($id);
    }

    public function create(array $data): ParkingLot
    {
        return $this->parkingLotRepository->create($data);
    }

    public function update(string $id, array $data): ?ParkingLot
    {
        return $this->parkingLotRepository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->parkingLotRepository->delete($id);
    }

    /**
     * Check availability for a time window.
     *
     * @return array{parkingLotId: string, checkIn: string, checkOut: string, available: bool, remainingSpaces: int, pricing: array|null}|null
     */
    public function getAvailability(string $parkingLotId, string $checkIn, string $checkOut, ?string $vehicleType = null): ?array
    {
        $lot = $this->parkingLotRepository->findById($parkingLotId);

        if (!$lot) {
            return null;
        }

        $capacity = $lot->capacity ?? ['totalSpaces' => 0, 'availableSpaces' => 0];
        $totalSpaces = $capacity['totalSpaces'] ?? 0;
        $availableSpaces = $capacity['availableSpaces'] ?? 0;

        // Count overlapping reservations
        $overlapping = $lot->reservations()
            ->whereNotIn('status', ['cancelled', 'expired', 'checked_out'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->count();

        $remaining = max(0, $totalSpaces - $overlapping);

        return [
            'parkingLotId' => $lot->id,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'available' => $remaining > 0,
            'remainingSpaces' => $remaining,
            'pricing' => $lot->pricing,
        ];
    }
}
