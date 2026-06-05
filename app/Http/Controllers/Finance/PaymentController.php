<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $payments = Payment::with(['invoice.client','invoice.project','receiver'])->latest()->paginate(20);
        $stats = [
            'total_received'  => Payment::sum('amount'),
            'this_month'      => Payment::whereMonth('payment_date', now()->month)->sum('amount'),
            'count'           => Payment::count(),
        ];
        return view('finance.payments.index', compact('payments','stats'));
    }

    public function store(StorePaymentRequest $request)
    {
        $invoice = Invoice::findOrFail($request->invoice_id);
        $this->invoiceService->recordPayment($invoice, $request->validated());
        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.created_successfully'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['invoice.client','invoice.project']);
        return view('finance.payments.index', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();
        // Recalculate
        $totalPaid = $invoice->payments()->sum('amount');
        $invoice->update([
            'paid_amount'     => $totalPaid,
            'remaining_amount'=> max(0, $invoice->total - $totalPaid),
            'payment_status'  => $totalPaid >= $invoice->total ? 'paid' : ($totalPaid > 0 ? 'partially_paid' : 'unpaid'),
        ]);
        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.deleted_successfully'));
    }
}
