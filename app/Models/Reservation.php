<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'reservation_number',
        'parking_lot_id',
        'driver_id',
        'vehicle_id',
        'status',
        'check_in',
        'check_out',
        'access_code',
        'total_price_amount',
        'total_price_currency',
        'cancelled_at',
        'cancellation_reason',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'cancelled_at' => 'datetime',
            'total_price_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<ParkingLot, $this>
     */
    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    /**
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
