<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
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
            'fleetNumber' => 'nullable|string|max:255',
            'type' => ['required', 'string', Rule::in(['truck', 'truck_trailer', 'van', 'adr_truck', 'refrigerated_truck'])],
            'licensePlate' => 'required|string|max:20',
            'trailerPlate' => 'nullable|string|max:20',
            'adr' => 'nullable|boolean',
            'refrigerated' => 'nullable|boolean',
            'heightCm' => 'nullable|integer|min:0',
            'lengthCm' => 'nullable|integer|min:0',
            'weightKg' => 'nullable|integer|min:0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        return [
            'fleet_number' => $validated['fleetNumber'] ?? null,
            'type' => $validated['type'],
            'license_plate' => $validated['licensePlate'],
            'trailer_plate' => $validated['trailerPlate'] ?? null,
            'adr' => $validated['adr'] ?? false,
            'refrigerated' => $validated['refrigerated'] ?? false,
            'height_cm' => $validated['heightCm'] ?? null,
            'length_cm' => $validated['lengthCm'] ?? null,
            'weight_kg' => $validated['weightKg'] ?? null,
        ];
    }
}
