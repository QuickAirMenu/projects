<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ProjectStatus;
use Illuminate\Http\Request;

class ProjectStatusController extends Controller
{
    public function index()
    {
        $statuses = ProjectStatus::orderBy('sort_order')->get();
        return view('settings.project-statuses', compact('statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar'    => 'required|string|max:100',
            'name_en'    => 'required|string|max:100',
            'slug'       => 'required|string|max:100|unique:project_statuses,slug',
            'color'      => 'required|string|max:7',
            'icon'       => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'boolean',
        ]);

        ProjectStatus::create($request->all());

        return redirect()->route('settings.project-statuses.index')
            ->with('success', __('common.created_successfully'));
    }

    public function update(Request $request, ProjectStatus $projectStatus)
    {
        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'color'   => 'required|string|max:7',
        ]);

        $projectStatus->update($request->all());

        return redirect()->route('settings.project-statuses.index')
            ->with('success', __('common.updated_successfully'));
    }

    public function destroy(ProjectStatus $projectStatus)
    {
        $projectStatus->delete();
        return redirect()->route('settings.project-statuses.index')
            ->with('success', __('common.deleted_successfully'));
    }
}
