<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:20',
            'whatsapp'     => 'nullable|string|max:20',
            'city'         => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'address'      => 'nullable|string',
            'status'       => 'required|in:active,potential,inactive,vip',
            'industry'     => 'nullable|string|max:100',
            'source'       => 'nullable|string|max:100',
            'notes'        => 'nullable|string',
            'assigned_to'  => 'nullable|exists:users,id',
        ];
    }
}
