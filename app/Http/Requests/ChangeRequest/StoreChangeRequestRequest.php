<?php

namespace App\Http\Requests\ChangeRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'requested_by'     => 'required|in:client,internal',
            'scope_type'       => 'required|in:in_scope,out_of_scope',
            'priority'         => 'required|in:low,medium,high',
            'impact_analysis'  => 'nullable|string',
            'additional_cost'  => 'nullable|numeric|min:0',
            'additional_hours' => 'nullable|numeric|min:0',
        ];
    }
}
