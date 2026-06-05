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
            $taxPercentage    = $data['tax_percentage'] ?? 0;
            $taxAmount       = round($subtotal * $taxPercentage / 100, 2);
            $discountAmount  = $data['discount_amount'] ?? 0;
            $total           = $subtotal + $taxAmount - $discountAmount;

            $invoice = Invoice::create(array_merge($data, [
                'subtotal'         => $subtotal,
                'tax_amount'       => $taxAmount,
                'total'            => $total,
                'remaining_amount' => $total,
                'paid_amount'      => 0,
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
     * Update an invoice and rebuild its line items atomically.
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->items()->delete();

            $subtotal       = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $taxPercentage  = $data['tax_percentage'] ?? $invoice->tax_percentage;
            $taxAmount      = round($subtotal * $taxPercentage / 100, 2);
            $discountAmount = $data['discount_amount'] ?? $invoice->discount_amount;
            $total          = $subtotal + $taxAmount - $discountAmount;
            $paidAmount     = $invoice->paid_amount ?? 0;

            $paymentStatus = 'unpaid';
            if ($paidAmount >= $total) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partially_paid';
            } elseif (!empty($data['due_date']) && $data['due_date'] < now()->toDateString()) {
                $paymentStatus = 'overdue';
            }

            $invoice->update(array_merge(array_diff_key($data, ['items' => '']), [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'remaining_amount' => max(0, $total - $paidAmount),
                'payment_status' => $paymentStatus,
            ]));

            foreach ($data['items'] as $order => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $order,
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
            $data['payment_number'] = $this->generatePaymentNumber();
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

    protected function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . date('Ym') . '-';
        $last   = Payment::where('payment_number', 'like', $prefix . '%')
            ->orderByDesc('payment_number')->first();
        $seq = $last ? (int) substr($last->payment_number, -4) + 1 : 1;

        do {
            $number = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $seq++;
        } while (Payment::where('payment_number', $number)->exists());

        return $number;
    }
}
