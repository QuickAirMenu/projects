<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    protected array $allowList = [
        'client_id',
        'subject',
        'description',
        'tax_percentage',
        'discount_type',
        'discount_percentage',
        'valid_until',
        'notes',
        'terms_and_conditions',
        'items',
    ];

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id'              => 'required|exists:clients,id',
            'subject'                => 'required|string|max:255',
            'description'            => 'nullable|string',
            'tax_percentage'         => 'nullable|numeric|min:0|max:100',
            'discount_type'          => 'nullable|in:percentage,fixed',
            'discount_percentage'    => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('discount_type') === 'percentage' && $value > 100) {
                        $fail(__('validation.discount_percentage_max', ['max' => 100]));
                    }
                    if ($this->input('discount_type') === 'fixed' && $value > 9999999) {
                        $fail(__('validation.discount_fixed_max'));
                    }
                },
            ],
            'valid_until'            => 'nullable|date|after:today',
            'notes'                  => 'nullable|string',
            'terms_and_conditions'   => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.description'    => 'required|string',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ];
    }

    protected function passedValidation(): void
    {
        $extras = array_diff_key($this->validated(), array_flip($this->allowList));
        $this->merge(array_diff_key($this->all(), $extras));
    }
}
