<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Project;
use App\Models\RecurringExpense;
use App\Services\RecurringExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringExpenseController extends Controller
{
    public function index()
    {
        $recurring = RecurringExpense::with(['project:id,name', 'client:id,name'])
            ->orderByDesc('is_active')
            ->orderBy('next_run_date')
            ->paginate(20);

        $stats = [
            'active' => RecurringExpense::where('is_active', true)->count(),
            'due' => RecurringExpense::due()->count(),
            'monthly' => RecurringExpense::where('is_active', true)->where('frequency', 'monthly')->sum('amount'),
        ];

        return view('finance.expenses.recurring.index', compact('recurring', 'stats'));
    }

    public function create()
    {
        return view('finance.expenses.recurring.form', $this->formData(new RecurringExpense([
            'frequency' => 'monthly',
            'next_run_date' => now()->toDateString(),
            'category' => 'software',
            'tax_amount' => 0,
            'billable' => false,
            'is_active' => true,
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        RecurringExpense::create($data);

        return redirect()->route('recurring-expenses.index')->with('success', __('expenses.recurring_created'));
    }

    public function edit(RecurringExpense $recurringExpense)
    {
        return view('finance.expenses.recurring.form', $this->formData($recurringExpense));
    }

    public function update(Request $request, RecurringExpense $recurringExpense)
    {
        $recurringExpense->update($this->validated($request));

        return redirect()->route('recurring-expenses.index')->with('success', __('expenses.recurring_updated'));
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        $recurringExpense->delete();

        return back()->with('success', __('expenses.recurring_deleted'));
    }

    public function runNow(RecurringExpense $recurringExpense, RecurringExpenseService $service)
    {
        try {
            $expense = $service->generate($recurringExpense);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('expenses.recurring_failed'));
        }

        return redirect()->route('expenses.index')
            ->with('success', __('expenses.recurring_generated', ['number' => $expense->expense_number]));
    }

    protected function formData(RecurringExpense $recurring): array
    {
        return [
            'recurring' => $recurring,
            'projects' => Project::with('client:id,name')->orderBy('name')->get(['id', 'name', 'client_id']),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'categories' => Expense::CATEGORIES,
        ];
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'vendor_name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:'.implode(',', Expense::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:monthly,quarterly,yearly'],
            'next_run_date' => ['required', 'date'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'billable' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['billable'] = $request->boolean('billable');
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
