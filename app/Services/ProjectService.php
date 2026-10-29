<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectWorkflowStep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Default workflow steps for every new project.
     */
    protected array $defaultWorkflowSteps = [
        ['name_ar' => 'استقبال الطلب',         'name_en' => 'Request Received'],
        ['name_ar' => 'تحليل الاحتياج',         'name_en' => 'Requirement Analysis'],
        ['name_ar' => 'التسعير والاعتماد',       'name_en' => 'Pricing & Approval'],
        ['name_ar' => 'تهيئة المشروع',           'name_en' => 'Project Setup'],
        ['name_ar' => 'Kickoff',                 'name_en' => 'Kickoff'],
        ['name_ar' => 'التنفيذ',                  'name_en' => 'Execution'],
        ['name_ar' => 'المراجعة الداخلية',        'name_en' => 'Internal Review'],
        ['name_ar' => 'إرسال للعميل',             'name_en' => 'Sent to Client'],
        ['name_ar' => 'التعديلات',                'name_en' => 'Revisions'],
        ['name_ar' => 'التسليم النهائي',           'name_en' => 'Final Delivery'],
        ['name_ar' => 'الإغلاق',                  'name_en' => 'Closure'],
        ['name_ar' => 'تقييم ما بعد المشروع',     'name_en' => 'Post-Project Evaluation'],
    ];

    /**
     * Create a new project and seed default workflow steps.
     */
    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $teamMembers = $data['team_members'] ?? [];
            unset($data['team_members']);

            $data['created_by']     = Auth::id();
            $data['project_number'] = $this->generateProjectNumber();

            $project = Project::create($data);

            // Attach team members
            if (!empty($teamMembers)) {
                $project->teamMembers()->sync($teamMembers);
            }

            // Create default workflow steps
            foreach ($this->defaultWorkflowSteps as $order => $step) {
                $project->workflowSteps()->create([
                    'step_name'  => app()->getLocale() === 'ar' ? $step['name_ar'] : $step['name_en'],
                    'step_order' => $order + 1,
                ]);
            }

            return $project;
        });
    }

    /**
     * Update an existing project.
     */
    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $teamMembers = $data['team_members'] ?? null;
            unset($data['team_members']);

            $project->update($data);

            if ($teamMembers !== null) {
                $project->teamMembers()->sync($teamMembers);
            }

            return $project->fresh();
        });
    }

    /**
     * Generate unique project number: PRJ-YYYYMM-XXXX.
     */
    protected function generateProjectNumber(): string
    {
        $prefix = 'PRJ-' . date('Ym') . '-';
        $last   = Project::withTrashed()->where('project_number', 'like', $prefix . '%')
            ->orderByDesc('project_number')
            ->first();

        $seq = $last ? (int) substr($last->project_number, -4) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate and update project progress based on task completion.
     */
    public function recalculateProgress(Project $project): void
    {
        $total     = $project->tasks()->whereNull('parent_task_id')->count();
        $completed = $project->tasks()->whereNull('parent_task_id')
            ->whereHas('status', fn($q) => $q->where('slug', 'done'))
            ->count();

        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
        $project->update(['progress_percentage' => $percentage]);
    }
}
