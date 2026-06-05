<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
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
        return view('finance.invoices.create', compact('projects','clients'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->createInvoice($request->validated());
        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client','project','items','payments']);
        return view('finance.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $projects = Project::orderBy('name')->get();
        $clients  = Client::orderBy('name')->get();
        return view('finance.invoices.create', compact('invoice','projects','clients'));
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        $invoice->items()->delete();
        $data            = $request->validated();
        $subtotal        = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $taxAmount       = round($subtotal * ($data['tax_percentage'] ?? 0) / 100, 2);
        $discountAmount  = $data['discount_amount'] ?? 0;
        $total           = $subtotal + $taxAmount - $discountAmount;

        $invoice->update(array_merge(array_diff_key($data, ['items'=>'']), [
            'subtotal' => $subtotal, 'tax_amount' => $taxAmount, 'total' => $total,
            'remaining_amount' => $total - $invoice->paid_amount,
        ]));
        foreach ($data['items'] as $i => $item) {
            $invoice->items()->create(['description'=>$item['description'],'quantity'=>$item['quantity'],'unit_price'=>$item['unit_price'],'total'=>$item['quantity']*$item['unit_price'],'sort_order'=>$i]);
        }
        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')
            ->with('success', __('messages.deleted_successfully'));
    }
}
