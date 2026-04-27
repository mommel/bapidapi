<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Driver;
use App\Repositories\DriverRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DriverService
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
    ) {}

    public function list(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->driverRepository->paginate($perPage, $search);
    }

    public function findById(string $id): ?Driver
    {
        return $this->driverRepository->findById($id);
    }

    public function create(array $data): Driver
    {
        return $this->driverRepository->create($data);
    }

    public function update(string $id, array $data): ?Driver
    {
        return $this->driverRepository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->driverRepository->delete($id);
    }
}
