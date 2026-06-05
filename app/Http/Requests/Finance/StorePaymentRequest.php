<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'invoice_id'            => 'required|exists:invoices,id',
            'amount'                => 'required|numeric|min:0.01',
            'payment_date'          => 'required|date',
            'payment_method'        => 'required|in:bank_transfer,cash,cheque,card,online,other',
            'transaction_reference' => 'nullable|string|max:255',
            'notes'                 => 'nullable|string',
        ];
    }
}
