<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    public function index(Request $request)
    {
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

        // Scope by role
        $user = auth()->user();
        if (!$user->hasAnyRole(['super-admin', 'admin', 'finance'])) {
            $query->where(fn($q) => $q
                ->where('manager_id', $user->id)
                ->orWhereHas('teamMembers', fn($t) => $t->where('users.id', $user->id))
            );
        }

        $projects  = $query->paginate(15)->withQueryString();
        $statuses  = ProjectStatus::where('is_active', true)->orderBy('sort_order')->get();
        $clients   = Client::orderBy('name')->get();

        $stats = [
            'total'     => Project::count(),
            'active'    => Project::whereHas('status', fn($q) => $q->whereIn('slug', ['in-progress','sent-to-client','revisions']))->count(),
            'overdue'   => Project::where('deadline', '<', now())->whereHas('status', fn($q) => $q->whereNotIn('slug', ['completed','closed','cancelled']))->count(),
            'completed' => Project::whereHas('status', fn($q) => $q->where('slug', 'completed'))->count(),
        ];

        return view('projects.index', compact('projects', 'statuses', 'clients', 'stats'));
    }

    public function create()
    {
        $clients      = Client::where('status', '!=', 'inactive')->orderBy('name')->get();
        $statuses     = ProjectStatus::where('is_active', true)->orderBy('sort_order')->get();
        $serviceTypes = ServiceType::where('is_active', true)->orderBy('sort_order')->get();
        $managers     = User::where('is_active', true)->whereHas('roles', fn($q) => $q->whereIn('name', ['super-admin','admin','project-manager']))->orderBy('name')->get();
        $teamMembers  = User::where('is_active', true)->orderBy('name')->get();

        return view('projects.create', compact('clients', 'statuses', 'serviceTypes', 'managers', 'teamMembers'));
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->createProject($request->validated());
        return redirect()->route('projects.show', $project)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load(['client','status','serviceType','manager','teamMembers','attachments.uploader','workflowSteps']);
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
}

    /**
     * Project Kanban view — groups all projects by status.
     */
    public function kanban()
    {
        $statuses  = \App\Models\ProjectStatus::where('is_active', true)->orderBy('sort_order')->get();
        $projectsRaw = Project::with(['client', 'status'])->get();
        $projects  = $projectsRaw->groupBy('status_id');
        return view('projects.kanban', compact('statuses', 'projects'));
    }
