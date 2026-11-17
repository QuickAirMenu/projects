<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\RecurringInvoice;
use App\Services\RecurringInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringInvoiceController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('view-invoices'), 403);

        $recurring = RecurringInvoice::with(['client:id,name', 'project:id,name'])
            ->orderByDesc('is_active')
            ->orderBy('next_run_date')
            ->paginate(20);

        $stats = [
            'active' => RecurringInvoice::where('is_active', true)->count(),
            'due' => RecurringInvoice::due()->count(),
            'total' => RecurringInvoice::count(),
        ];

        return view('finance.recurring.index', compact('recurring', 'stats'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('create-invoices'), 403);

        return view('finance.recurring.form', $this->formData(new RecurringInvoice([
            'invoice_type' => 'additional',
            'frequency' => 'monthly',
            'next_run_date' => now()->toDateString(),
            'tax_percentage' => 0,
            'discount_amount' => 0,
            'items' => [],
            'is_active' => true,
        ])));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('create-invoices'), 403);

        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        RecurringInvoice::create($data);

        return redirect()->route('recurring-invoices.index')
            ->with('success', __('messages.recurring_created'));
    }

    public function edit(RecurringInvoice $recurringInvoice)
    {
        abort_unless(auth()->user()->can('edit-invoices'), 403);

        return view('finance.recurring.form', $this->formData($recurringInvoice));
    }

    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
        abort_unless(auth()->user()->can('edit-invoices'), 403);

        $recurringInvoice->update($this->validated($request));

        return redirect()->route('recurring-invoices.index')
            ->with('success', __('messages.recurring_updated'));
    }

    public function destroy(RecurringInvoice $recurringInvoice)
    {
        abort_unless(auth()->user()->can('delete-invoices'), 403);

        $recurringInvoice->delete();

        return back()->with('success', __('messages.recurring_deleted'));
    }

    public function runNow(RecurringInvoice $recurringInvoice, RecurringInvoiceService $service)
    {
        abort_unless(auth()->user()->can('create-invoices'), 403);

        try {
            $invoice = $service->generate($recurringInvoice);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('messages.recurring_run_failed'));
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.recurring_generated', ['number' => $invoice->invoice_number]));
    }

    protected function formData(RecurringInvoice $recurring): array
    {
        return [
            'recurring' => $recurring,
            'clients' => Client::orderBy('name')->get(),
            'projects' => Project::with('client:id,name')->orderBy('name')->get(),
        ];
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'client_id' => ['required', 'exists:clients,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'invoice_type' => ['required', 'in:initial,partial,final,additional'],
            'frequency' => ['required', 'in:monthly,quarterly,yearly'],
            'next_run_date' => ['required', 'date'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['tax_percentage'] = $data['tax_percentage'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['discount_type'] = $data['discount_type'] ?? 'fixed';
        $data['items'] = array_values($data['items']);

        return $data;
    }
}
