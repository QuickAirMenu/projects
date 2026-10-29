<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Mail\ProjectHandoffMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\Task;
use App\Models\User;
use App\Services\MailConfigService;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::with(['client', 'status', 'manager', 'serviceType'])->latest();

        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('project_number', 'like', "%{$request->search}%")
            );
        }
        if ($request->status_id) $query->where('status_id', $request->status_id);
        if ($request->priority)   $query->where('priority', $request->priority);
        if ($request->client_id)  $query->where('client_id', $request->client_id);

        $showArchived = $request->has('archived') && $request->archived == '1';
        if ($showArchived) {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        // Scope by role
        $query->visibleTo(auth()->user());

        $projects  = $query->paginate(15)->withQueryString();
        $statuses  = ProjectStatus::where('is_active', true)->orderBy('sort_order')->get();
        $clients   = Client::orderBy('name')->get();

        $stats = [
            'total'     => Project::count(),
            'active'    => Project::whereHas('status', fn($q) => $q->whereIn('slug', ['in-progress','sent-to-client','revisions']))->count(),
            'overdue'   => Project::where('deadline', '<', now())->whereHas('status', fn($q) => $q->whereNotIn('slug', ['completed','closed','cancelled']))->count(),
            'completed' => Project::whereHas('status', fn($q) => $q->where('slug', 'completed'))->count(),
            'archived'  => Project::where('is_archived', true)->count(),
        ];

        return view('projects.index', compact('projects', 'statuses', 'clients', 'stats', 'showArchived'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        $clients      = Client::where('status', '!=', 'inactive')->orderBy('name')->get();
        $statuses     = ProjectStatus::where('is_active', true)->orderBy('sort_order')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('sort_order')->get();
        $managers     = User::where('is_active', true)->whereHas('roles', fn($q) => $q->whereIn('name', ['super-admin','admin','project-manager']))->orderBy('name')->get();
        $teamMembers  = User::where('is_active', true)->orderBy('name')->get();

        $sourceQuote = null;
        if (request()->filled('quote_id')) {
            $sourceQuote = \App\Models\Quote::with('client')->find(request('quote_id'));
        }

        return view('projects.create', compact('clients', 'statuses', 'serviceTypes', 'managers', 'teamMembers', 'sourceQuote'));
    }

    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);

        $project = $this->projectService->createProject($request->validated());

        if ($request->filled('source_quote_id')) {
            $quote = \App\Models\Quote::find($request->input('source_quote_id'));
            if ($quote && !$quote->converted_to_project_id) {
                $quote->update(['converted_to_project_id' => $project->id]);
            }
        }

        return redirect()->route('projects.show', $project)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load(['client','status','serviceType','manager','teamMembers','attachments.uploadedBy','invoices']);
        $project->setRelation('rootTasks', $project->tasks()
            ->whereNull('parent_task_id')
            ->with(['status', 'assignedUser'])
            ->orderBy('due_date')
            ->take(10)
            ->get());
        $project->setRelation('workflowSteps', $project->workflowSteps()
            ->orderBy('step_order')
            ->get());
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $clients      = Client::orderBy('name')->get();
        $statuses     = ProjectStatus::where('is_active', true)->orderBy('sort_order')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('sort_order')->get();
        $managers     = User::where('is_active', true)->whereHas('roles', fn($q) => $q->whereIn('name', ['super-admin','admin','project-manager']))->orderBy('name')->get();
        $teamMembers  = User::where('is_active', true)->orderBy('name')->get();
        $selectedTeam = $project->teamMembers->pluck('id')->toArray();

        return view('projects.edit', compact('project','clients','statuses','serviceTypes','managers','teamMembers','selectedTeam'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        $this->projectService->updateProject($project, $request->validated());
        return redirect()->route('projects.show', $project)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function archive(Project $project)
    {
        $this->authorize('update', $project);
        $project->update(['is_archived' => true, 'archived_at' => now()]);
        return back()->with('success', __('project.archived_success'));
    }

    public function unarchive(Project $project)
    {
        $this->authorize('update', $project);
        $project->update(['is_archived' => false, 'archived_at' => null]);
        return back()->with('success', __('project.unarchived_success'));
    }

    public function handoff(Project $project)
    {
        $this->authorize('update', $project);

        $project->load([
            'client',
            'status',
            'manager',
            'teamMembers',
            'attachments.uploadedBy',
            'workflowSteps',
            'invoices.payments',
            'tasks.status',
            'quotes',
        ]);

        return view('projects.handoff', compact('project'));
    }

    public function completeHandoff(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'handoff_notes' => 'nullable|string',
        ]);

        $deliveredStatus = ProjectStatus::where('slug', 'delivered')->first()
            ?? ProjectStatus::where('slug', 'completed')->first();

        $project->update([
            'is_handed_off' => true,
            'handed_off_at' => now(),
            'handoff_notes' => $data['handoff_notes'] ?? null,
            'status_id' => $deliveredStatus?->id ?? $project->status_id,
        ]);

        if ($project->client?->email) {
            try {
                app(MailConfigService::class)->applyDynamicMailConfig();
                if (config('mail.mailers.smtp.host')) {
                    Mail::to($project->client->email)->send(new ProjectHandoffMail($project->fresh(['client', 'manager', 'teamMembers'])));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('projects.show', $project)
            ->with('success', __('project.handoff_success'));
    }

    /**
     * Project Kanban view - groups all projects by status.
     */
    public function kanban()
    {
        $statuses = ProjectStatus::where('is_active', true)->orderBy('sort_order')->get();
        $query = Project::with(['client', 'status'])->visibleTo(auth()->user());

        $projects = $query->get()->groupBy('status_id');

        return view('projects.kanban', compact('statuses', 'projects'));
    }

    public function calendar()
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::visibleTo(auth()->user())->orderBy('name')->get(['id', 'name']);
        return view('projects.calendar', compact('projects'));
    }

    public function calendarData(Request $request)
    {
        $query = Task::with(['project:id,name', 'status:id,name_en,name_ar'])
            ->whereNotNull('due_date');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->get();

        $events = $tasks->map(function ($task) {
            $colors = [
                'urgent' => '#ef4444',
                'high'   => '#f97316',
                'medium' => '#3b82f6',
                'low'    => '#6b7280',
            ];
            $color = $colors[$task->priority] ?? '#6b7280';

            $locale = app()->getLocale();
            $statusName = $locale === 'ar' ? ($task->status->name_ar ?? '') : ($task->status->name_en ?? '');

            return [
                'id'      => (string) $task->id,
                'title'   => $task->project->name . ' — ' . $task->title,
                'start'   => $task->due_date->format('Y-m-d'),
                'url'     => route('tasks.show', $task),
                'color'   => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'project'  => $task->project->name,
                    'status'   => $statusName,
                    'priority' => $task->priority,
                ],
            ];
        });

        return response()->json($events);
    }

    public function timeline()
    {
        $user = auth()->user();
        $query = Project::with(['status', 'tasks' => function ($q) {
            $q->whereNotNull('start_date')->orWhereNotNull('due_date')->orderBy('start_date');
        }])->visibleTo($user);

        $projects = $query->get();
        return view('projects.timeline', compact('projects'));
    }
}
