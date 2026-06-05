<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\TaskStatus;
use Illuminate\Http\Request;

class TaskStatusController extends Controller
{
    public function index()
    {
        $statuses = TaskStatus::orderBy('sort_order')->get();
        return view('settings.task-statuses', compact('statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar'    => 'required|string|max:100',
            'name_en'    => 'required|string|max:100',
            'slug'       => 'required|string|max:100|unique:task_statuses,slug',
            'color'      => 'required|string|max:7',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'boolean',
        ]);

        TaskStatus::create($data);

        return redirect()->route('settings.task-statuses.index')
            ->with('success', __('common.created_successfully'));
    }

    public function update(Request $request, TaskStatus $taskStatus)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'color'   => 'required|string|max:7',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $taskStatus->update($data);

        return redirect()->route('settings.task-statuses.index')
            ->with('success', __('common.updated_successfully'));
    }

    public function destroy(TaskStatus $taskStatus)
    {
        $taskStatus->delete();
        return redirect()->route('settings.task-statuses.index')
            ->with('success', __('common.deleted_successfully'));
    }
}
