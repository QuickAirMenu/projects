<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalPushController extends Controller
{
    public function key()
    {
        $public = (string) config('webpush.public_key');

        if ($public === '') {
            return response()->json(['message' => 'Push is not configured.'], 503);
        }

        return response()->json(['publicKey' => $public]);
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:500'],
            'keys.auth' => ['required', 'string', 'max:500'],
            'contentEncoding' => ['nullable', 'string', 'max:20'],
        ]);

        $account = Auth::guard('client')->user();

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'notifiable_type' => $account->getMorphClass(),
                'notifiable_id' => $account->getKey(),
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? 'aesgcm',
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
        ]);

        Auth::guard('client')->user()->pushSubscriptions()
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['ok' => true]);
    }
}
