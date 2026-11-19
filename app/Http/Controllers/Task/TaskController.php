<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Account;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::with(['project', 'status', 'assignedUser'])
            ->whereNull('parent_task_id')
            ->latest();

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->overdue) {
            $query->where('due_date', '<', now());
        }

        $user = Auth::user();
        $query->visibleTo($user);

        $tasks = $query->paginate(20)->withQueryString();
        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();
        $projects = Project::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total' => Task::whereNull('parent_task_id')->count(),
            'my_open' => Task::whereNull('parent_task_id')->where('assigned_to', $user->id)->whereHas('status', fn ($q) => $q->whereNotIn('slug', ['done']))->count(),
            'overdue' => Task::whereNull('parent_task_id')->where('due_date', '<', now())->whereHas('status', fn ($q) => $q->whereNotIn('slug', ['done']))->count(),
            'done' => Task::whereNull('parent_task_id')->whereHas('status', fn ($q) => $q->where('slug', 'done'))->count(),
        ];

        return view('tasks.index', compact('tasks', 'statuses', 'projects', 'users', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        $projects = Project::orderBy('name')->get();
        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('tasks.create', compact('projects', 'statuses', 'users'));
    }

    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $task = Task::create($data);

        return redirect()->route('tasks.show', $task)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

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
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('tasks.edit', compact('task', 'statuses', 'users'));
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
        $projects = Project::orderBy('name')->get(['id', 'name']);

        return view('tasks.calendar', compact('projects'));
    }

    public function calendarData(Request $request)
    {
        $query = Task::with(['project:id,name', 'status:id,name_en,name_ar'])
            ->whereNotNull('due_date');

        $user = Auth::user();
        if ($user->hasRole('team-member')) {
            $query->where('assigned_to', $user->id);
        }

        $tasks = $query->get();

        $events = $tasks->map(function ($task) {
            $colors = [
                'urgent' => '#ef4444',
                'high' => '#f97316',
                'medium' => '#3b82f6',
                'low' => '#6b7280',
            ];
            $color = $colors[$task->priority] ?? '#6b7280';

            return [
                'id' => (string) $task->id,
                'title' => $task->title,
                'start' => $task->due_date->format('Y-m-d'),
                'url' => route('tasks.show', $task),
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'project' => $task->project->name ?? '',
                    'priority' => $task->priority,
                ],
            ];
        });

        return response()->json($events);
    }

    public function timeline(Request $request)
    {
        $query = Task::with(['project', 'status', 'assignedUser'])
            ->whereNotNull('start_date')
            ->orWhereNotNull('due_date')
            ->orderBy('start_date');

        $user = Auth::user();
        if ($user->hasRole('team-member')) {
            $query->where('assigned_to', $user->id);
        }

        $tasks = $query->get();

        return view('tasks.timeline', compact('tasks'));
    }

    public function payoutForm(Task $task)
    {
        $this->authorize('update', $task);

        return view('tasks.payout', [
            'task' => $task,
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'baseCurrency' => app(TreasuryService::class)->baseCurrency(),
        ]);
    }

    public function payCost(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.000001'],
            'payout_date' => ['required', 'date'],
        ]);

        $account = Account::findOrFail($data['account_id']);

        app(TreasuryService::class)->recordTaskPayout(
            $task,
            $account,
            (float) $data['amount'],
            (float) ($data['exchange_rate'] ?? 1),
            $data['payout_date']
        );

        return redirect()->route('tasks.show', $task)->with('success', __('task.cost_marked_paid'));
    }

    public function unpayCost(Task $task)
    {
        $this->authorize('update', $task);

        app(TreasuryService::class)->reverseTaskPayout($task);

        return back()->with('success', __('task.cost_marked_unpaid'));
    }
}
