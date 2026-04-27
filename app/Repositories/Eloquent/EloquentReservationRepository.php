<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Reservation;
use App\Repositories\ReservationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentReservationRepository implements ReservationRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Reservation::with(['parkingLot', 'driver', 'vehicle']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['driverId'])) {
            $query->where('driver_id', $filters['driverId']);
        }

        if (! empty($filters['vehicleId'])) {
            $query->where('vehicle_id', $filters['vehicleId']);
        }

        if (! empty($filters['parkingLotId'])) {
            $query->where('parking_lot_id', $filters['parkingLotId']);
        }

        if (! empty($filters['from'])) {
            $query->where('check_in', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('check_out', '<=', $filters['to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(string $id): ?Reservation
    {
        return Reservation::with(['parkingLot', 'driver', 'vehicle'])->find($id);
    }

    public function create(array $data): Reservation
    {
        $reservation = Reservation::create($data);

        return $reservation->load(['parkingLot', 'driver', 'vehicle']);
    }

    public function update(string $id, array $data): ?Reservation
    {
        $reservation = Reservation::find($id);

        if (! $reservation) {
            return null;
        }

        $reservation->update($data);

        return $reservation->fresh(['parkingLot', 'driver', 'vehicle']);
    }

    public function delete(string $id): bool
    {
        $reservation = Reservation::find($id);

        if (! $reservation) {
            return false;
        }

        return (bool) $reservation->delete();
    }
}
