<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ClientPortalAccount extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'password',
        'is_active',
        'email_verified_at',
        'setup_token',
        'setup_token_expires_at',
        'last_login_at',
    ];

    protected $hidden = ['password', 'setup_token'];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'setup_token_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getAuthIdentifierName()
    {
        return 'client_id';
    }

    public function getEmailAttribute()
    {
        return $this->client?->email;
    }

    public function getClientNameAttribute()
    {
        return $this->client?->name;
    }
}
