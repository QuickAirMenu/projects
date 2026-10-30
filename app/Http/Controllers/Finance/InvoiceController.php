<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Setting;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['client', 'project'])->latest();
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
            'unpaid' => Invoice::where('payment_status', 'unpaid')->sum('remaining_amount'),
            'overdue' => Invoice::where('payment_status', 'overdue')->count(),
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

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project', 'items', 'payments', 'contract']);

        return view('finance.invoices.show', compact('invoice'));
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
}
