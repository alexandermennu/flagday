<?php

namespace App\Http\Requests;

use App\Enums\AttendeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'organization' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'required_if:status,confirmed', 'string', 'max:255'],
            'decline_reason' => ['nullable', 'string', 'max:1000'],
            'status' => [
                'required',
                Rule::in([AttendeeStatus::Confirmed->value, AttendeeStatus::Declined->value]),
            ],
            'guests' => ['nullable', 'array', 'max:5'],
            'guests.*.full_name' => ['required_with:guests', 'string', 'max:255'],
            'guests.*.organization' => ['required_with:guests', 'string', 'max:255'],
            'guests.*.position' => ['required_with:guests', 'string', 'max:255'],
        ];
    }
}
