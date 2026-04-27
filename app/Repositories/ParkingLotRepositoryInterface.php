<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ParkingLot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ParkingLotRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function findById(string $id): ?ParkingLot;

    public function create(array $data): ParkingLot;

    public function update(string $id, array $data): ?ParkingLot;

    public function delete(string $id): bool;
}
