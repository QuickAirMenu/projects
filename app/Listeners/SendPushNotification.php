<?php

namespace App\Listeners;

use App\Services\WebPushService;
use Illuminate\Notifications\Events\NotificationSent;

class SendPushNotification
{
    public function handle(NotificationSent $event): void
    {
        if (($event->channel ?? null) !== 'database') {
            return;
        }

        $notifiable = $event->notifiable;

        if (! is_object($notifiable) || ! method_exists($notifiable, 'pushSubscriptions')) {
            return;
        }

        try {
            if (! $notifiable->pushSubscriptions()->exists()) {
                return;
            }

            $notification = $event->notification;
            $data = method_exists($notification, 'toArray') ? (array) $notification->toArray($notifiable) : [];

            $title = $data['title'] ?? ucfirst(str_replace('_', ' ', class_basename($notification)));
            $body = $data['message'] ?? '';
            $url = $data['url'] ?? '/';

            app(WebPushService::class)->send($notifiable, [
                'title' => (string) $title,
                'body' => (string) $body,
                'url' => (string) $url,
                'tag' => class_basename($notification),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
