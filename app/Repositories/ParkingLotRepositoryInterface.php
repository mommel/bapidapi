<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ParkingLotRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function findById(string $id): ?\App\Models\ParkingLot;

    public function create(array $data): \App\Models\ParkingLot;

    public function update(string $id, array $data): ?\App\Models\ParkingLot;

    public function delete(string $id): bool;
}
