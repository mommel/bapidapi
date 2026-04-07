<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingLotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => [
                'street' => $this->address_street,
                'postalCode' => $this->address_postal_code,
                'city' => $this->address_city,
                'state' => $this->address_state,
                'countryCode' => $this->address_country_code,
            ],
            'coordinates' => [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ],
            'securityLevel' => $this->security_level,
            'amenities' => $this->amenities ?? [],
            'openingHours' => $this->opening_hours,
            'capacity' => $this->capacity,
            'operatorName' => $this->operator_name,
            'contactPhone' => $this->contact_phone,
            'checkInInstructions' => $this->check_in_instructions,
            'pricing' => $this->pricing,
        ];
    }
}
