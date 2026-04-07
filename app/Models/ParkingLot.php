<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ParkingLotFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingLot extends Model
{
    /** @use HasFactory<ParkingLotFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'address_street',
        'address_postal_code',
        'address_city',
        'address_state',
        'address_country_code',
        'latitude',
        'longitude',
        'security_level',
        'amenities',
        'opening_hours',
        'capacity',
        'operator_name',
        'contact_phone',
        'check_in_instructions',
        'pricing',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'opening_hours' => 'array',
            'capacity' => 'array',
            'pricing' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
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
