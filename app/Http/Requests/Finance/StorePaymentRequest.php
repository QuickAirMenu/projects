<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    protected array $allowList = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'notes',
        'proof_of_payment',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,cheque,card,online,other',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'proof_of_payment' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
        ];
    }

    protected function passedValidation(): void
    {
        $extras = array_diff_key($this->validated(), array_flip($this->allowList));
        $this->merge(array_diff_key($this->all(), $extras));
    }
}
