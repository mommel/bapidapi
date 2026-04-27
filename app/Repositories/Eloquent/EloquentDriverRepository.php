<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Driver;
use App\Repositories\DriverRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentDriverRepository implements DriverRepositoryInterface
{
    public function paginate(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = Driver::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('employee_number', 'ilike', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(string $id): ?Driver
    {
        return Driver::find($id);
    }

    public function create(array $data): Driver
    {
        return Driver::create($data);
    }

    public function update(string $id, array $data): ?Driver
    {
        $driver = Driver::find($id);

        if (! $driver) {
            return null;
        }

        $driver->update($data);

        return $driver->fresh();
    }

    public function delete(string $id): bool
    {
        $driver = Driver::find($id);

        if (! $driver) {
            return false;
        }

        return (bool) $driver->delete();
    }
}
