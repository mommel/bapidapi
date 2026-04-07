<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ParkingLot;
use App\Repositories\ParkingLotRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentParkingLotRepository implements ParkingLotRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = ParkingLot::query();

        if (!empty($filters['countryCode'])) {
            $query->where('address_country_code', $filters['countryCode']);
        }

        if (!empty($filters['city'])) {
            $query->where('address_city', 'ilike', "%{$filters['city']}%");
        }

        if (!empty($filters['minSecurityLevel'])) {
            $levels = ['basic', 'guarded', 'fenced', 'gated', 'cctv', 'secure'];
            $minIndex = array_search($filters['minSecurityLevel'], $levels, true);
            if ($minIndex !== false) {
                $validLevels = array_slice($levels, (int) $minIndex);
                $query->whereIn('security_level', $validLevels);
            }
        }

        if (!empty($filters['latitude']) && !empty($filters['longitude']) && !empty($filters['radiusKm'])) {
            $lat = (float) $filters['latitude'];
            $lng = (float) $filters['longitude'];
            $radius = (int) $filters['radiusKm'];

            // Haversine formula for PostgreSQL
            $query->selectRaw('*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance', [$lat, $lng, $lat])
            ->havingRaw('(
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) <= ?', [$lat, $lng, $lat, $radius])
            ->orderBy('distance');
        } else {
            $query->orderBy('name');
        }

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?ParkingLot
    {
        return ParkingLot::find($id);
    }

    public function create(array $data): ParkingLot
    {
        return ParkingLot::create($data);
    }

    public function update(string $id, array $data): ?ParkingLot
    {
        $lot = ParkingLot::find($id);

        if (!$lot) {
            return null;
        }

        $lot->update($data);

        return $lot->fresh();
    }

    public function delete(string $id): bool
    {
        $lot = ParkingLot::find($id);

        if (!$lot) {
            return false;
        }

        return (bool) $lot->delete();
    }
}
