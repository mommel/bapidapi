<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReservationRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function findById(string $id): ?\App\Models\Reservation;

    public function create(array $data): \App\Models\Reservation;

    public function update(string $id, array $data): ?\App\Models\Reservation;

    public function delete(string $id): bool;
}
