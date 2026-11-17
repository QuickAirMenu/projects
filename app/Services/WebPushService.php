<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function enabled(): bool
    {
        return filled(config('webpush.public_key')) && filled(config('webpush.private_key'));
    }

    public function send(object $notifiable, array $payload): int
    {
        if (! $this->enabled() || ! method_exists($notifiable, 'pushSubscriptions')) {
            return 0;
        }

        $subscriptions = $notifiable->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => (string) config('webpush.subject'),
                    'publicKey' => (string) config('webpush.public_key'),
                    'privateKey' => (string) config('webpush.private_key'),
                ],
            ], ['TTL' => (int) config('webpush.ttl', 3600)]);

            $byEndpoint = [];

            foreach ($subscriptions as $subscription) {
                $byEndpoint[$subscription->endpoint] = $subscription;

                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding ?: 'aesgcm',
                    ]),
                    json_encode($payload, JSON_UNESCAPED_UNICODE)
                );
            }

            $sent = 0;

            foreach ($webPush->flush() as $report) {
                $endpoint = (string) $report->getRequest()->getUri();

                if ($report->isSuccess()) {
                    $sent++;

                    continue;
                }

                $status = $report->getResponse()?->getStatusCode();

                if (in_array($status, [404, 410], true) && isset($byEndpoint[$endpoint])) {
                    PushSubscription::whereKey($byEndpoint[$endpoint]->id)->delete();
                }
            }

            return $sent;
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }
}
