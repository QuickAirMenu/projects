<?php

namespace App\Http\Requests\Client;

use App\Rules\DeliverableEmail;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', new DeliverableEmail],
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'status' => 'required|in:active,potential,inactive,vip',
            'industry' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['website', 'linkedin', 'twitter', 'instagram', 'facebook'] as $field) {
            $value = trim((string) $this->input($field));

            if ($value !== '' && ! preg_match('/^https?:\/\//i', $value)) {
                $value = 'https://'.$value;
            }

            $this->merge([$field => $value !== '' ? $value : null]);
        }
    }
}
