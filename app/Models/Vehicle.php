<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'fleet_number',
        'type',
        'license_plate',
        'trailer_plate',
        'adr',
        'refrigerated',
        'height_cm',
        'length_cm',
        'weight_kg',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'adr' => 'boolean',
            'refrigerated' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
