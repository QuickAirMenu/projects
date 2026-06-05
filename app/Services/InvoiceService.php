<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create an invoice with its line items.
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $data['created_by']     = Auth::id();
            $data['invoice_number'] = $this->generateInvoiceNumber();

            // Calculate totals
            $subtotal        = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $taxAmount       = round($subtotal * ($data['tax_percentage'] ?? 0) / 100, 2);
            $discountAmount  = $data['discount_amount'] ?? 0;
            $total           = $subtotal + $taxAmount - $discountAmount;

            $invoice = Invoice::create(array_merge($data, [
                'subtotal'         => $subtotal,
                'tax_amount'       => $taxAmount,
                'total'            => $total,
                'remaining_amount' => $total,
            ]));

            // Create line items
            foreach ($data['items'] as $order => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total'       => $item['quantity'] * $item['unit_price'],
                    'sort_order'  => $order,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Record a payment and update invoice payment status.
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $data['payment_number'] = 'PAY-' . date('Ym') . '-' . str_pad(Payment::count() + 1, 4, '0', STR_PAD_LEFT);
            $data['received_by']    = Auth::id();

            $payment    = $invoice->payments()->create($data);
            $totalPaid  = $invoice->payments()->sum('amount');
            $remaining  = max(0, $invoice->total - $totalPaid);

            $status = 'unpaid';
            if ($totalPaid >= $invoice->total) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partially_paid';
            } elseif ($invoice->due_date && $invoice->due_date < now()) {
                $status = 'overdue';
            }

            $invoice->update([
                'paid_amount'    => $totalPaid,
                'remaining_amount' => $remaining,
                'payment_status' => $status,
            ]);

            return $payment;
        });
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $last   = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')->first();
        $seq = $last ? (int) substr($last->invoice_number, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
