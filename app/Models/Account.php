<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public const TYPES = [
        'bank', 'cash', 'wallet', 'payment_gateway', 'credit_card',
        'petty_cash', 'escrow', 'investment', 'loan', 'other',
    ];

    public const TYPE_META = [
        'bank' => ['nature' => 'asset', 'color' => 'blue', 'icon' => 'fa-building-columns'],
        'cash' => ['nature' => 'asset', 'color' => 'emerald', 'icon' => 'fa-money-bill-wave'],
        'wallet' => ['nature' => 'asset', 'color' => 'purple', 'icon' => 'fa-mobile-screen'],
        'payment_gateway' => ['nature' => 'asset', 'color' => 'indigo', 'icon' => 'fa-globe'],
        'credit_card' => ['nature' => 'liability', 'color' => 'red', 'icon' => 'fa-credit-card'],
        'petty_cash' => ['nature' => 'asset', 'color' => 'amber', 'icon' => 'fa-coins'],
        'escrow' => ['nature' => 'asset', 'color' => 'cyan', 'icon' => 'fa-shield-halved'],
        'investment' => ['nature' => 'asset', 'color' => 'teal', 'icon' => 'fa-chart-line'],
        'loan' => ['nature' => 'liability', 'color' => 'rose', 'icon' => 'fa-hand-holding-dollar'],
        'other' => ['nature' => 'neutral', 'color' => 'gray', 'icon' => 'fa-circle'],
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

    public function meta(string $key, string $default = ''): string
    {
        return (string) (self::TYPE_META[$this->type][$key] ?? $default);
    }

    public function nature(): string
    {
        return $this->meta('nature', 'neutral');
    }

    public function isLiability(): bool
    {
        return $this->nature() === 'liability';
    }

    public function color(): string
    {
        return $this->meta('color', 'gray');
    }

    public function icon(): string
    {
        return $this->meta('icon', 'fa-circle');
    }

    public function canDelete(): bool
    {
        return $this->transactions()->count() === 0;
    }
}
