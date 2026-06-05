<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'subject',
        'message',
        'communication_date',
        'follow_up_date',
    ];

    protected $casts = [
        'communication_date' => 'datetime',
        'follow_up_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', now());
    }
}
