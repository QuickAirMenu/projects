<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    public const TYPES = [
        'commercial_registration', 'tax_certificate', 'national_id', 'contract', 'other',
    ];

    protected $fillable = [
        'client_id', 'type', 'title', 'number', 'issue_date', 'expiry_date',
        'file_path', 'file_name', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
