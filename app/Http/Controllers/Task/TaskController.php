<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project','status','assignedUser'])
            ->whereNull('parent_task_id')
            ->latest();

        if ($request->search)     $query->where('title', 'like', "%{$request->search}%");
        if ($request->status_id)  $query->where('status_id', $request->status_id);
        if ($request->priority)   $query->where('priority', $request->priority);
        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->assigned_to)$query->where('assigned_to', $request->assigned_to);
        if ($request->overdue)    $query->where('due_date', '<', now());

        $user = Auth::user();
        if ($user->hasRole('team-member')) {
            $query->where('assigned_to', $user->id);
        }

        $tasks    = $query->paginate(20)->withQueryString();
        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();
        $projects = Project::orderBy('name')->get();
        $users    = User::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total'   => Task::whereNull('parent_task_id')->count(),
            'my_open' => Task::whereNull('parent_task_id')->where('assigned_to', $user->id)->whereHas('status', fn($q) => $q->whereNotIn('slug',['done']))->count(),
            'overdue' => Task::whereNull('parent_task_id')->where('due_date','<',now())->whereHas('status', fn($q) => $q->whereNotIn('slug',['done']))->count(),
            'done'    => Task::whereNull('parent_task_id')->whereHas('status', fn($q) => $q->where('slug','done'))->count(),
        ];

        return view('tasks.index', compact('tasks','statuses','projects','users','stats'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();
        $users    = User::where('is_active', true)->orderBy('name')->get();
        return view('tasks.create', compact('projects','statuses','users'));
    }

    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $task = Task::create($data);
        return redirect()->route('tasks.show', $task)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Task $task)
    {
        $task->load(['project', 'status', 'assignedUser', 'subtasks.status']);
        $task->setRelation('checklists', $task->checklists()->orderBy('sort_order')->get());
        $task->setRelation('timeLogs', $task->timeLogs()->with('user')->latest()->take(5)->get());
        $task->setRelation('comments', $task->comments()->with('user')->latest()->get());
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();
        $users    = User::where('is_active', true)->orderBy('name')->get();
        return view('tasks.edit', compact('task','statuses','users'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task->update($request->validated());
        return redirect()->route('tasks.show', $task)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return redirect()->route('tasks.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function calendar()
    {
        $user  = Auth::user();
        $query = Task::with(['status','project'])->whereNotNull('due_date');
        if ($user->hasRole('team-member')) $query->where('assigned_to', $user->id);
        $tasks = $query->get()->map(fn($t) => [
            'id'    => $t->id,
            'title' => $t->title,
            'start' => $t->due_date->toDateString(),
            'color' => $t->status->color ?? '#3B82F6',
            'url'   => route('tasks.show', $t->id),
        ]);
        return view('tasks.calendar', compact('tasks'));
    }
}
