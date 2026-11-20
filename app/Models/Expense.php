<?php

namespace App\Models;

use App\Models\Concerns\ScopedToAccessibleProjects;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use ScopedToAccessibleProjects;

    public const CATEGORIES = [
        'software', 'hosting', 'marketing', 'travel', 'office', 'equipment', 'subcontractor', 'utilities', 'other',
    ];

    protected $fillable = [
        'expense_number', 'vendor_name', 'category', 'expense_date', 'amount', 'tax_amount', 'total',
        'status', 'paid_at', 'payment_method', 'account_id', 'exchange_rate', 'reference', 'notes', 'project_id', 'client_id',
        'billable', 'receipt_path', 'receipt_name', 'created_by', 'approved_by', 'approved_at', 'invoiced_invoice_id', 'recurring_expense_id',
        'rejection_reason',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'paid_at' => 'date',
        'approved_at' => 'datetime',
        'billable' => 'boolean',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'total' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiced_invoice_id');
    }

    public function recurring()
    {
        return $this->belongsTo(RecurringExpense::class, 'recurring_expense_id');
    }

    public function isInvoiced(): bool
    {
        return $this->invoiced_invoice_id !== null;
    }

    public function scopeBillable($query)
    {
        return $query->where('status', 'approved')->where('billable', true)->whereNull('invoiced_invoice_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public static function nextNumber(): string
    {
        $year = now()->format('Y');
        $base = 'EXP-'.$year.'-';

        for ($i = 1; $i <= 500; $i++) {
            $candidate = $base.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            if (! DB::table('expenses')->where('expense_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $base.strtoupper(bin2hex(random_bytes(3)));
    }
}
