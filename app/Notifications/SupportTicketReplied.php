<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportTicketReplied extends Notification
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
            'type' => 'support_ticket_replied',
            'title' => __('portal.ticket_replied_title'),
            'message' => __('portal.ticket_replied_msg', ['subject' => $this->ticket->subject]),
            'url' => route('portal.tickets'),
            'icon' => 'fa-comment-dots',
            'color' => 'emerald',
        ];
    }
}
