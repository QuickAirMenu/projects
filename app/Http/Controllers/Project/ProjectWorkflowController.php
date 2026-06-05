<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectWorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectWorkflowController extends Controller
{
    /**
     * Display the workflow for a given project.
     */
    public function show(Project $project)
    {
        $steps = $project->workflowSteps()->orderBy('step_order')->get();
        return view('projects.workflow', compact('project', 'steps'));
    }

    /**
     * Mark a workflow step as completed.
     */
    public function completeStep(Project $project, ProjectWorkflowStep $step)
    {
        $this->authorize('update', $project);

        $step->update([
            'completed'    => true,
            'completed_by' => Auth::id(),
            'completed_at' => now(),
        ]);

        return redirect()->route('projects.workflow.show', $project)
            ->with('success', __('project.workflow_step_completed'));
    }

    /**
     * Undo (un-complete) a workflow step.
     */
    public function undoStep(Project $project, ProjectWorkflowStep $step)
    {
        $this->authorize('update', $project);

        $step->update([
            'completed'    => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);

        return redirect()->route('projects.workflow.show', $project)
            ->with('success', __('project.workflow_step_undone'));
    }
}
