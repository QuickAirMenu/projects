<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    protected array $allowList = [
        'project_id',
        'parent_task_id',
        'title',
        'description',
        'status_id',
        'assigned_to',
        'priority',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
        ];
    }

    protected function passedValidation(): void
    {
        $extras = array_diff_key($this->validated(), array_flip($this->allowList));
        $this->merge(array_diff_key($this->all(), $extras));
    }
}
