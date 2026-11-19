<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountTransaction extends Model
{
    protected $fillable = [
        'account_id', 'transaction_date', 'direction', 'amount', 'currency', 'exchange_rate',
        'base_amount', 'category', 'description', 'reference_type', 'reference_id',
        'transfer_group', 'reconciled_at', 'created_by', 'attachment_path', 'attachment_name',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_amount' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeUnreconciled($query)
    {
        return $query->whereNull('reconciled_at');
    }
}
