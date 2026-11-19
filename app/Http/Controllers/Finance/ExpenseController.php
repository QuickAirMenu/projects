<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Project;
use App\Services\ExpenseBillingService;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['project:id,name', 'creator:id,name', 'approver:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->date('to')));

        $totals = [
            'pending' => (clone $query)->where('status', 'pending')->sum('total'),
            'approved' => (clone $query)->where('status', 'approved')->sum('total'),
            'count' => (clone $query)->count(),
        ];

        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('finance.expenses.index', [
            'expenses' => $expenses,
            'totals' => $totals,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'categories' => Expense::CATEGORIES,
            'filters' => $request->only(['status', 'category', 'project_id', 'from', 'to']),
        ]);
    }

    public function create()
    {
        return view('finance.expenses.form', [
            'expense' => new Expense([
                'expense_date' => now()->toDateString(),
                'status' => 'pending',
                'tax_amount' => 0,
                'billable' => false,
            ]),
            'projects' => Project::with('client:id,name')->orderBy('name')->get(['id', 'name', 'client_id']),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['expense_number'] = Expense::nextNumber();
        $data['total'] = round((float) $data['amount'] + (float) $data['tax_amount'], 2);
        $data['created_by'] = Auth::id();
        $data['status'] = 'pending';

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $data['receipt_path'] = $file->store('expenses', 'private');
            $data['receipt_name'] = $file->getClientOriginalName();
        }

        $expense = Expense::create($data);

        if ($expense->paid_at && $expense->account_id) {
            app(TreasuryService::class)->recordExpensePayment($expense);
        }

        return redirect()->route('expenses.index')->with('success', __('expenses.created'));
    }

    public function edit(Expense $expense)
    {
        if (! $expense->isPending()) {
            return redirect()->route('expenses.index')->with('error', __('expenses.cannot_edit'));
        }

        return view('finance.expenses.form', [
            'expense' => $expense,
            'projects' => Project::with('client:id,name')->orderBy('name')->get(['id', 'name', 'client_id']),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        if (! $expense->isPending()) {
            return back()->with('error', __('expenses.cannot_edit'));
        }

        $data = $this->validated($request);
        $data['total'] = round((float) $data['amount'] + (float) $data['tax_amount'], 2);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('private')->delete($expense->receipt_path);
            }
            $file = $request->file('receipt');
            $data['receipt_path'] = $file->store('expenses', 'private');
            $data['receipt_name'] = $file->getClientOriginalName();
        }

        $expense->update($data);

        if ($expense->paid_at && $expense->account_id) {
            app(TreasuryService::class)->recordExpensePayment($expense->fresh());
        }

        return redirect()->route('expenses.index')->with('success', __('expenses.updated'));
    }

    public function destroy(Expense $expense)
    {
        if (! $expense->isPending()) {
            return back()->with('error', __('expenses.cannot_edit'));
        }

        if ($expense->receipt_path) {
            Storage::disk('private')->delete($expense->receipt_path);
        }

        $expense->delete();

        return back()->with('success', __('expenses.deleted'));
    }

    public function approve(Expense $expense)
    {
        if (! $expense->isPending()) {
            return back()->with('error', __('expenses.already_processed'));
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        activity()->causedBy(Auth::user())->performedOn($expense)->log('expense approved');

        return back()->with('success', __('expenses.approved_message'));
    }

    public function reject(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        if (! $expense->isPending()) {
            return back()->with('error', __('expenses.already_processed'));
        }

        $expense->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        activity()->causedBy(Auth::user())->performedOn($expense)->log('expense rejected');

        return back()->with('success', __('expenses.rejected_message'));
    }

    public function downloadReceipt(Expense $expense)
    {
        abort_unless($expense->receipt_path && Storage::disk('private')->exists($expense->receipt_path), 404);

        return Storage::disk('private')->download($expense->receipt_path, $expense->receipt_name ?: 'receipt');
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

        $expenses = Expense::with(['project:id,name'])
            ->where('status', 'approved')
            ->where('billable', true)
            ->whereNull('invoiced_invoice_id')
            ->when($data['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->orderBy('project_id')
            ->orderBy('expense_date')
            ->get();

        $total = round((float) $expenses->sum('amount'), 2);
        $tax = round($total * 0.15, 2);

        return view('finance.expenses.bill', [
            'expenses' => $expenses,
            'from' => $from,
            'to' => $to,
            'projectId' => $data['project_id'] ?? null,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'total' => $total,
            'tax' => $tax,
        ]);
    }

    public function bill(Request $request, ExpenseBillingService $service)
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
            ->with('success', __('expenses.invoice_created', ['number' => $invoice->invoice_number]));
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'vendor_name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:'.implode(',', Expense::CATEGORIES)],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.000001'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'billable' => ['nullable', 'boolean'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $data['billable'] = $request->boolean('billable');
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['exchange_rate'] = $data['exchange_rate'] ?? 1;

        if (! empty($data['project_id']) && empty($data['client_id'])) {
            $data['client_id'] = Project::whereKey($data['project_id'])->value('client_id');
        }

        return $data;
    }
}
