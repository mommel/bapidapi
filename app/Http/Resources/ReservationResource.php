<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservationNumber' => $this->reservation_number,
            'parkingLotId' => $this->parking_lot_id,
            'driverId' => $this->driver_id,
            'vehicleId' => $this->vehicle_id,
            'status' => $this->status,
            'checkIn' => $this->check_in?->toISOString(),
            'checkOut' => $this->check_out?->toISOString(),
            'accessCode' => $this->access_code,
            'totalPrice' => [
                'amount' => $this->total_price_amount ? (float) $this->total_price_amount : null,
                'currency' => $this->total_price_currency,
            ],
            'createdAt' => $this->created_at?->toISOString(),
            'cancelledAt' => $this->cancelled_at?->toISOString(),
            'cancellationReason' => $this->cancellation_reason,
            'notes' => $this->notes,
        ];
    }
}
