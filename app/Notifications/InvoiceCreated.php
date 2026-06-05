<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceCreated extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'invoice_created',
            'title'   => __('notifications.invoice_created'),
            'message' => __('notifications.invoice_created_msg', ['number' => $this->invoice->invoice_number]),
            'url'     => route('invoices.show', $this->invoice->id),
            'icon'    => 'fa-file-invoice',
            'color'   => 'green',
        ];
    }
}
