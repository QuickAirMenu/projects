<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $query = Invoice::with(['client','project'])->latest();
        if ($request->payment_status) $query->where('payment_status', $request->payment_status);
        if ($request->client_id)      $query->where('client_id', $request->client_id);
        if ($request->invoice_type)   $query->where('invoice_type', $request->invoice_type);

        $invoices = $query->paginate(15)->withQueryString();
        $clients  = Client::orderBy('name')->get();
        $stats    = [
            'total_invoiced' => Invoice::sum('total'),
            'total_paid'     => Invoice::sum('paid_amount'),
            'unpaid'         => Invoice::where('payment_status','unpaid')->sum('remaining_amount'),
            'overdue'        => Invoice::where('payment_status','overdue')->count(),
        ];
        return view('finance.invoices.index', compact('invoices','clients','stats'));
    }

    public function create()
    {
        $projects = Project::with('client')->orderBy('name')->get();
        $clients  = Client::orderBy('name')->get();

        $prefillData = null;
        if (request()->filled('quote_id')) {
            $quote = Quote::with(['items.serviceType', 'client'])->findOrFail(request('quote_id'));
            $prefillData = [
                'quote' => $quote,
                'client_id' => request('client_id', $quote->client_id),
                'description' => request('description', $quote->subject),
                'items' => $quote->items->map(fn($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->toArray(),
            ];
        }

        return view('finance.invoices.create', compact('projects','clients','prefillData'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->createInvoice($request->validated());
        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client','project','items','payments','contract']);
        return view('finance.invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['client', 'project', 'items', 'createdBy']);
        $settings = Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','company_address','currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'settings'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        $projects = Project::orderBy('name')->get();
        $clients  = Client::orderBy('name')->get();
        return view('finance.invoices.create', compact('invoice','projects','clients'));
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = $this->invoiceService->updateInvoice($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function convertToContract(Invoice $invoice)
    {
        if ($invoice->contract) {
            return redirect()->route('contracts.show', $invoice->contract)
                ->with('success', 'هذه الفاتورة مرتبطة بعقد بالفعل');
        }

        return redirect()->route('contracts.create', [
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'project_id' => $invoice->project_id,
            'contract_value' => $invoice->total,
        ]);
    }
}
