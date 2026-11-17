<?php

namespace App\Models\Concerns;

use App\Models\PushSubscription;

trait HasPushSubscriptions
{
    public function pushSubscriptions()
    {
        return $this->morphMany(PushSubscription::class, 'notifiable');
    }
}
