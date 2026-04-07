<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
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
            'employeeNumber' => 'nullable|string|max:255',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'language' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Map camelCase input to snake_case for the model.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        return [
            'employee_number' => $validated['employeeNumber'] ?? null,
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'language' => $validated['language'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }
}
