<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Vehicle;

class VehicleService
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepository,
    ) {}

    public function list(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->vehicleRepository->paginate($perPage, $search);
    }

    public function findById(string $id): ?Vehicle
    {
        return $this->vehicleRepository->findById($id);
    }

    public function create(array $data): Vehicle
    {
        return $this->vehicleRepository->create($data);
    }

    public function update(string $id, array $data): ?Vehicle
    {
        return $this->vehicleRepository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->vehicleRepository->delete($id);
    }
}
