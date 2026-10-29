<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    protected array $allowList = [
        'quote_id',
        'project_id',
        'client_id',
        'invoice_type',
        'invoice_date',
        'due_date',
        'tax_percentage',
        'discount_type',
        'discount_amount',
        'notes',
        'terms',
        'items',
    ];

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'quote_id'         => 'nullable|exists:quotes,id',
            'project_id'       => 'required|exists:projects,id',
            'client_id'        => 'required|exists:clients,id',
            'invoice_type'     => 'required|in:initial,partial,final,additional',
            'invoice_date'     => 'required|date',
            'due_date'         => 'nullable|date|after_or_equal:invoice_date',
            'tax_percentage'   => 'nullable|numeric|min:0|max:100',
            'discount_type'    => 'nullable|in:percentage,fixed',
            'discount_amount'  => [
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
            'notes'            => 'nullable|string',
            'terms'            => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ];
    }

    protected function passedValidation(): void
    {
        $extras = array_diff_key($this->validated(), array_flip($this->allowList));
        $this->merge(array_diff_key($this->all(), $extras));
    }
}
