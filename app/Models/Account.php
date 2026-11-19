<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public const TYPES = [
        'bank', 'cash', 'wallet', 'payment_gateway', 'credit_card',
        'petty_cash', 'escrow', 'investment', 'loan', 'other',
    ];

    protected $fillable = [
        'name', 'type', 'currency', 'bank_name', 'account_number', 'iban',
        'opening_balance', 'exchange_rate_to_base', 'is_active', 'notes', 'created_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'exchange_rate_to_base' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function balance(): float
    {
        $in = (float) $this->transactions()->where('direction', 'in')->sum('amount');
        $out = (float) $this->transactions()->where('direction', 'out')->sum('amount');

        return round((float) $this->opening_balance + $in - $out, 2);
    }

    public function baseBalance(): float
    {
        return round($this->balance() * (float) $this->exchange_rate_to_base, 2);
    }
}
