<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_percentage' => 'nullable|numeric|min:0',
            'valid_until' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }
}
