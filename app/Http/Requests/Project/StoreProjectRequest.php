<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'client_id'       => 'required|exists:clients,id',
            'service_type_id' => 'required|exists:service_types,id',
            'description'     => 'nullable|string',
            'start_date'      => 'nullable|date',
            'deadline'        => 'nullable|date|after_or_equal:start_date',
            'priority'        => 'required|in:low,medium,high,urgent',
            'status_id'       => 'required|exists:project_statuses,id',
            'manager_id'      => 'required|exists:users,id',
            'budget'          => 'nullable|numeric|min:0',
            'internal_notes'  => 'nullable|string',
            'client_notes'    => 'nullable|string',
            'team_members'    => 'nullable|array',
            'team_members.*'  => 'exists:users,id',
        ];
    }
}
