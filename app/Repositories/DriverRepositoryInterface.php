<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Driver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DriverRepositoryInterface
{
    public function paginate(int $perPage = 20, ?string $search = null): LengthAwarePaginator;

    public function findById(string $id): ?Driver;

    public function create(array $data): Driver;

    public function update(string $id, array $data): ?Driver;

    public function delete(string $id): bool;
}
