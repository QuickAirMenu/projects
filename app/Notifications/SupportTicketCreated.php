<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportTicketCreated extends Notification
{
    use Queueable;

    public function __construct(public SupportTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'support_ticket_created',
            'title' => __('notifications.ticket_created'),
            'message' => __('notifications.ticket_created_msg', [
                'subject' => $this->ticket->subject,
                'client' => $this->ticket->client?->name,
            ]),
            'url' => route('support-tickets.index'),
            'icon' => 'fa-life-ring',
            'color' => 'indigo',
        ];
    }
}
