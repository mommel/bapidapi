<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReservationRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function findById(string $id): ?Reservation;

    public function create(array $data): Reservation;

    public function update(string $id, array $data): ?Reservation;

    public function delete(string $id): bool;
}
