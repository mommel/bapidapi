<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
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
            'employeeNumber' => 'sometimes|nullable|string|max:255',
            'firstName' => 'sometimes|string|max:255',
            'lastName' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'email' => 'sometimes|nullable|email|max:255',
            'language' => 'sometimes|nullable|string|max:10',
            'notes' => 'sometimes|nullable|string',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();
        $mapped = [];

        if (array_key_exists('employeeNumber', $validated)) {
            $mapped['employee_number'] = $validated['employeeNumber'];
        }
        if (array_key_exists('firstName', $validated)) {
            $mapped['first_name'] = $validated['firstName'];
        }
        if (array_key_exists('lastName', $validated)) {
            $mapped['last_name'] = $validated['lastName'];
        }
        if (array_key_exists('phone', $validated)) {
            $mapped['phone'] = $validated['phone'];
        }
        if (array_key_exists('email', $validated)) {
            $mapped['email'] = $validated['email'];
        }
        if (array_key_exists('language', $validated)) {
            $mapped['language'] = $validated['language'];
        }
        if (array_key_exists('notes', $validated)) {
            $mapped['notes'] = $validated['notes'];
        }

        return $mapped;
    }
}
