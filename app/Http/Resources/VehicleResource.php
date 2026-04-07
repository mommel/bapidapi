<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fleetNumber' => $this->fleet_number,
            'type' => $this->type,
            'licensePlate' => $this->license_plate,
            'trailerPlate' => $this->trailer_plate,
            'adr' => $this->adr,
            'refrigerated' => $this->refrigerated,
            'heightCm' => $this->height_cm,
            'lengthCm' => $this->length_cm,
            'weightKg' => $this->weight_kg,
        ];
    }
}
