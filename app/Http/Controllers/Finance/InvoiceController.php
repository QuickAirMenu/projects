<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Mail\InvoiceIssuedMail;
use App\Mail\InvoiceReminderMail;
use App\Models\Account;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Services\InvoiceService;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['client', 'project'])->latest();
        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', $q)
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', $q)->orWhere('company_name', 'like', $q))
                    ->orWhereHas('project', fn ($p) => $p->where('name', 'like', $q));
            });
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->invoice_type) {
            $query->where('invoice_type', $request->invoice_type);
        }
        if ($request->trashed) {
            $query->onlyTrashed();
        }

        $invoices = $query->paginate(15)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $stats = [
            'total_invoiced' => Invoice::sum('total'),
            'total_paid' => Invoice::sum('paid_amount'),
            'outstanding' => Invoice::where('remaining_amount', '>', 0)->sum('remaining_amount'),
            'overdue' => Invoice::where('remaining_amount', '>', 0)
                ->whereDate('due_date', '<', now()->toDateString())->count(),
            'never_paid' => Invoice::where('remaining_amount', '>', 0)->where('paid_amount', '<=', 0)->count(),
            'partial' => Invoice::where('remaining_amount', '>', 0)->where('paid_amount', '>', 0)->count(),
        ];
        $trashed = $request->has('trashed');

        return view('finance.invoices.index', compact('invoices', 'clients', 'stats', 'trashed'));
    }

    public function create()
    {
        $this->authorize('create', Invoice::class);

        $projects = Project::with('client')->orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        $availableQuotes = collect();

        $prefillData = null;
        if (request()->filled('quote_id')) {
            $quote = Quote::with(['items.serviceType', 'client'])->findOrFail(request('quote_id'));
            $prefillData = [
                'quote' => $quote,
                'client_id' => request('client_id', $quote->client_id),
                'description' => request('description', $quote->subject),
                'items' => $quote->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->toArray(),
            ];
        } elseif (request()->filled('project_id')) {
            $project = Project::with('client')->findOrFail(request('project_id'));
            $availableQuotes = Quote::with('items')
                ->where('client_id', $project->client_id)
                ->where('status', '!=', 'draft')
                ->latest()
                ->get();

            $prefillData = [
                'project_id' => $project->id,
                'client_id' => $project->client_id,
                'description' => $project->name,
            ];

            if (request()->filled('quote_id')) {
                $quote = $availableQuotes->firstWhere('id', request('quote_id'));
            } else {
                $quote = $project->quotes()->with('items')->latest()->first() ?: $availableQuotes->first();
            }

            if ($quote && $quote->items->count()) {
                $prefillData['items'] = $quote->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->toArray();
            }
        }

        return view('finance.invoices.create', compact('projects', 'clients', 'prefillData', 'availableQuotes'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->invoiceService->createInvoice($request->validated());

        if ($invoice->client?->email) {
            try {
                app(MailConfigService::class)->applyDynamicMailConfig();

                if (filled(config('mail.mailers.smtp.host'))) {
                    Mail::to($invoice->client->email)->send(new InvoiceIssuedMail($invoice));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project', 'items', 'payments', 'contract']);

        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $canForceDelete = $invoice->trashed() && ! $this->invoiceHasFinancialLinks($invoice);

        return view('finance.invoices.show', compact('invoice', 'accounts', 'canForceDelete'));
    }

    public function remind(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->load('client');

        if (! $invoice->client?->email) {
            return back()->with('error', __('messages.client_no_email'));
        }

        if ((float) $invoice->remaining_amount <= 0) {
            return back()->with('error', __('messages.invoice_already_paid'));
        }

        try {
            app(MailConfigService::class)->applyDynamicMailConfig();

            if (blank(config('mail.mailers.smtp.host'))) {
                return back()->with('error', __('messages.mail_not_configured'));
            }

            Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));

            activity()->causedBy(auth()->user())->performedOn($invoice)->log('invoice reminder sent');

            return back()->with('success', __('messages.invoice_reminder_sent'));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('messages.invoice_reminder_failed'));
        }
    }

    public function remindOverdue(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        try {
            app(MailConfigService::class)->applyDynamicMailConfig();
        } catch (\Throwable $e) {
            report($e);
        }

        if (blank(config('mail.mailers.smtp.host'))) {
            return back()->with('error', __('messages.mail_not_configured'));
        }

        $invoices = Invoice::with('client')
            ->where('remaining_amount', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->whereIn('payment_status', ['unpaid', 'partially_paid', 'overdue'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            if (! $invoice->client?->email) {
                continue;
            }

            try {
                Mail::to($invoice->client->email)->send(new InvoiceReminderMail($invoice));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        }

        $message = __('messages.reminders_sent_summary', ['sent' => $sent, 'failed' => $failed]);

        return back()->with($sent > 0 ? 'success' : 'error', $message);
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project', 'items', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name', 'company_name_ar', 'company_logo', 'company_email', 'company_phone', 'company_address', 'currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $html = view('pdf.invoice', compact('invoice', 'settings'))->render();
        $mpdf->WriteHTML($html);

        return response($mpdf->Output("invoice-{$invoice->invoice_number}.pdf", 'S'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->load('items');
        $projects = Project::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        return view('finance.invoices.create', compact('invoice', 'projects', 'clients'));
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice = $this->invoiceService->updateInvoice($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function forceDestroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        abort_unless($invoice->trashed(), 404);

        if ($this->invoiceHasFinancialLinks($invoice)) {
            return back()->with('error', __('messages.invoice_has_financial_links'));
        }

        $invoice->forceDelete();

        return redirect()->route('invoices.index', ['trashed' => 1])->with('success', __('messages.deleted_permanently'));
    }

    public function restore(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->restore();

        return redirect()->route('invoices.index')->with('success', __('messages.restored_successfully'));
    }

    public function convertToContract(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        if ($invoice->contract) {
            return redirect()->route('contracts.show', $invoice->contract)
                ->with('success', __('finance.invoice_already_has_contract'));
        }

        return redirect()->route('contracts.create', [
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'project_id' => $invoice->project_id,
            'contract_value' => $invoice->total,
        ]);
    }

    protected function invoiceHasFinancialLinks(Invoice $invoice): bool
    {
        return $invoice->payments()->withTrashed()->exists()
            || $invoice->reminders()->exists()
            || $invoice->contract()->exists()
            || TimeEntry::where('invoiced_invoice_id', $invoice->id)->exists()
            || Expense::where('invoiced_invoice_id', $invoice->id)->exists();
    }
}
