<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parkingLotId' => 'required|uuid|exists:parking_lots,id',
            'driverId' => 'required|uuid|exists:drivers,id',
            'vehicleId' => 'required|uuid|exists:vehicles,id',
            'checkIn' => 'required|date|after:now',
            'checkOut' => 'required|date|after:checkIn',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        return [
            'parking_lot_id' => $validated['parkingLotId'],
            'driver_id' => $validated['driverId'],
            'vehicle_id' => $validated['vehicleId'],
            'check_in' => $validated['checkIn'],
            'check_out' => $validated['checkOut'],
            'notes' => $validated['notes'] ?? null,
        ];
    }
}
