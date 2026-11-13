<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'client_id',
        'portal_account_id',
        'subject',
        'message',
        'status',
        'priority',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function portalAccount()
    {
        return $this->belongsTo(ClientPortalAccount::class, 'portal_account_id');
    }

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
