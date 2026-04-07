<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
            'fleetNumber' => 'sometimes|nullable|string|max:255',
            'type' => ['sometimes', 'string', Rule::in(['truck', 'truck_trailer', 'van', 'adr_truck', 'refrigerated_truck'])],
            'licensePlate' => 'sometimes|string|max:20',
            'trailerPlate' => 'sometimes|nullable|string|max:20',
            'adr' => 'sometimes|boolean',
            'refrigerated' => 'sometimes|boolean',
            'heightCm' => 'sometimes|nullable|integer|min:0',
            'lengthCm' => 'sometimes|nullable|integer|min:0',
            'weightKg' => 'sometimes|nullable|integer|min:0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();
        $mapped = [];

        $mapping = [
            'fleetNumber' => 'fleet_number',
            'type' => 'type',
            'licensePlate' => 'license_plate',
            'trailerPlate' => 'trailer_plate',
            'adr' => 'adr',
            'refrigerated' => 'refrigerated',
            'heightCm' => 'height_cm',
            'lengthCm' => 'length_cm',
            'weightKg' => 'weight_kg',
        ];

        foreach ($mapping as $camel => $snake) {
            if (array_key_exists($camel, $validated)) {
                $mapped[$snake] = $validated[$camel];
            }
        }

        return $mapped;
    }
}
