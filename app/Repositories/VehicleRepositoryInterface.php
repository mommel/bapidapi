<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VehicleRepositoryInterface
{
    public function paginate(int $perPage = 20, ?string $search = null): LengthAwarePaginator;

    public function findById(string $id): ?\App\Models\Vehicle;

    public function create(array $data): \App\Models\Vehicle;

    public function update(string $id, array $data): ?\App\Models\Vehicle;

    public function delete(string $id): bool;
}
