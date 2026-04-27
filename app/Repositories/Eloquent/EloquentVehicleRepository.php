<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Vehicle;
use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentVehicleRepository implements VehicleRepositoryInterface
{
    public function paginate(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = Vehicle::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('license_plate', 'ilike', "%{$search}%")
                    ->orWhere('fleet_number', 'ilike', "%{$search}%")
                    ->orWhere('trailer_plate', 'ilike', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(string $id): ?Vehicle
    {
        return Vehicle::find($id);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(string $id, array $data): ?Vehicle
    {
        $vehicle = Vehicle::find($id);

        if (! $vehicle) {
            return null;
        }

        $vehicle->update($data);

        return $vehicle->fresh();
    }

    public function delete(string $id): bool
    {
        $vehicle = Vehicle::find($id);

        if (! $vehicle) {
            return false;
        }

        return (bool) $vehicle->delete();
    }
}
