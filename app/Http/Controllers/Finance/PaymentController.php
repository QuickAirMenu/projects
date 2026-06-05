<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validated();
        unset($data['proof_of_payment']);

        $payment = $this->invoiceService->recordPayment($invoice, $data);

        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $path = $this->storeProofOfPayment($file, $payment->payment_number);

            $payment->update([
                'proof_file_name' => $file->getClientOriginalName(),
                'proof_file_path' => $path,
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.created_successfully'));
    }

    public function edit(Payment $payment)
    {
        $payment->load(['invoice.client', 'invoice.project']);

        return view('finance.payments.edit', compact('payment'));
    }

    public function update(StorePaymentRequest $request, Payment $payment)
    {
        $data = $request->validated();

        if ((int) $data['invoice_id'] !== (int) $payment->invoice_id) {
            return back()->withInput()->with('error', __('finance.payment_invoice_change_not_allowed'));
        }

        $payment->update([
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($request->hasFile('proof_of_payment')) {
            $oldPath = $payment->proof_file_path;
            $file = $request->file('proof_of_payment');
            $path = $this->storeProofOfPayment($file, $payment->payment_number);

            $payment->update([
                'proof_file_name' => $file->getClientOriginalName(),
                'proof_file_path' => $path,
            ]);

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $this->recalculateInvoiceTotals($payment->invoice);

        return redirect()->route('payments.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function show(Payment $payment)
    {
        return redirect()->route('payments.edit', $payment);
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;

        if ($payment->proof_file_path) {
            Storage::disk('public')->delete($payment->proof_file_path);
        }

        $payment->delete();

        $this->recalculateInvoiceTotals($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('messages.deleted_successfully'));
    }

    protected function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $totalPaid = $invoice->payments()->sum('amount');
        $invoice->update([
            'paid_amount'     => $totalPaid,
            'remaining_amount'=> max(0, $invoice->total - $totalPaid),
            'payment_status'  => $totalPaid >= $invoice->total ? 'paid' : ($totalPaid > 0 ? 'partially_paid' : 'unpaid'),
        ]);
    }

    protected function storeProofOfPayment(UploadedFile $file, string $paymentNumber): string
    {
        $directory = 'payments/' . $paymentNumber;

        if (!str_starts_with((string) $file->getMimeType(), 'image/') || blank(config('workspace.tinify.api_key'))) {
            return $file->store($directory, 'public');
        }

        $compressed = $this->compressImageWithTinify($file);
        if ($compressed === null) {
            return $file->store($directory, 'public');
        }

        $path = $directory . '/' . $file->hashName();
        Storage::disk('public')->put($path, $compressed);

        return $path;
    }

    protected function compressImageWithTinify(UploadedFile $file): ?string
    {
        $apiKey = config('workspace.tinify.api_key');
        if (blank($apiKey)) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($apiKey, '')
                ->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
                ->timeout(120)
                ->post('https://api.tinify.com/shrink');

            $location = $response->header('Location');
            if (!$response->successful() || blank($location)) {
                return null;
            }

            $download = Http::timeout(120)->get($location);
            if (!$download->successful()) {
                return null;
            }

            return $download->body();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
