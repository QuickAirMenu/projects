<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\TimeEntryBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filtered($request);

        $totals = (clone $query)
            ->selectRaw('COALESCE(SUM(hours), 0) as hours, COALESCE(SUM(hours * hourly_rate), 0) as amount')
            ->first();

        $entries = $query->with(['project:id,name', 'task:id,title', 'user:id,name'])
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('time.index', [
            'entries' => $entries,
            'totals' => $totals,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['project_id', 'user_id', 'from', 'to', 'unbilled']),
        ]);
    }

    public function create()
    {
        return view('time.form', [
            'entry' => new TimeEntry([
                'work_date' => now()->toDateString(),
                'hours' => 1,
                'billable' => true,
                'hourly_rate' => 0,
            ]),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'tasks' => Task::orderBy('title')->get(['id', 'title', 'project_id']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = Auth::id();
        $entry = TimeEntry::create($data);

        $this->syncTaskHours($entry->task_id);

        return redirect()->route('time-entries.index')->with('success', __('time.created'));
    }

    public function edit(TimeEntry $timeEntry)
    {
        return view('time.form', [
            'entry' => $timeEntry,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'tasks' => Task::orderBy('title')->get(['id', 'title', 'project_id']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, TimeEntry $timeEntry)
    {
        if ($timeEntry->isInvoiced()) {
            return back()->with('error', __('time.cannot_edit_invoiced'));
        }

        $oldTask = $timeEntry->task_id;
        $timeEntry->update($this->validated($request));
        $this->syncTaskHours($oldTask);
        $this->syncTaskHours($timeEntry->task_id);

        return redirect()->route('time-entries.index')->with('success', __('time.updated'));
    }

    public function destroy(TimeEntry $timeEntry)
    {
        if ($timeEntry->isInvoiced()) {
            return back()->with('error', __('time.cannot_edit_invoiced'));
        }

        $taskId = $timeEntry->task_id;
        $timeEntry->delete();
        $this->syncTaskHours($taskId);

        return back()->with('success', __('time.deleted'));
    }

    public function billPreview(Request $request)
    {
        $data = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = $data['from'] ?? now()->startOfMonth()->toDateString();
        $to = $data['to'] ?? now()->toDateString();

        $entries = TimeEntry::unbilled()
            ->with(['project:id,name', 'task:id,title', 'user:id,name'])
            ->when($data['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))
            ->whereBetween('work_date', [$from, $to])
            ->orderBy('project_id')
            ->orderBy('work_date')
            ->get();

        return view('time.bill', [
            'entries' => $entries,
            'from' => $from,
            'to' => $to,
            'projectId' => $data['project_id'] ?? null,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'totalHours' => $entries->sum(fn ($e) => (float) $e->hours),
            'totalAmount' => $entries->sum(fn ($e) => $e->amount()),
        ]);
    }

    public function bill(Request $request, TimeEntryBillingService $service)
    {
        abort_unless(Auth::user()->can('create-invoices'), 403);

        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $invoice = $service->bill(
            (int) $data['project_id'],
            $data['from'],
            $data['to'],
            (int) ($data['tax_percentage'] ?? 15),
            Auth::id(),
            $data['due_date'] ?? null
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('time.invoice_created', ['number' => $invoice->invoice_number]));
    }

    protected function filtered(Request $request)
    {
        return TimeEntry::query()
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('work_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('work_date', '<=', $request->date('to')))
            ->when($request->boolean('unbilled'), fn ($q) => $q->whereNull('invoiced_invoice_id')->where('billable', true));
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'user_id' => ['required', 'exists:users,id'],
            'work_date' => ['required', 'date'],
            'hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'description' => ['required', 'string', 'max:500'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'billable' => ['nullable', 'boolean'],
        ]);

        $data['billable'] = $request->boolean('billable');

        return $data;
    }

    protected function syncTaskHours(?int $taskId): void
    {
        if (! $taskId) {
            return;
        }

        Task::whereKey($taskId)->update([
            'actual_hours' => TimeEntry::where('task_id', $taskId)->sum('hours'),
        ]);
    }
}
