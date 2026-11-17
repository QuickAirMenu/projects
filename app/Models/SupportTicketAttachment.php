<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupportTicketAttachment extends Model
{
    protected $fillable = ['support_ticket_id', 'file_name', 'file_path', 'file_size', 'mime'];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (SupportTicketAttachment $attachment) {
            if ($attachment->file_path) {
                Storage::disk('private')->delete($attachment->file_path);
            }
        });
    }
}
