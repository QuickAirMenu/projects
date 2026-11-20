<?php

namespace App\Models;

use App\Models\Concerns\ScopedToAccessibleProjects;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RecurringExpense extends Model
{
    use ScopedToAccessibleProjects;

    protected $fillable = [
        'vendor_name', 'category', 'amount', 'tax_amount', 'frequency', 'next_run_date',
        'last_run_at', 'is_active', 'project_id', 'client_id', 'billable',
        'payment_method', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'next_run_date' => 'date',
        'last_run_at' => 'datetime',
        'is_active' => 'boolean',
        'billable' => 'boolean',
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

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)->whereDate('next_run_date', '<=', now()->toDateString());
    }

    public function total(): float
    {
        return round((float) $this->amount + (float) $this->tax_amount, 2);
    }

    public static function nextDate($from, string $frequency): Carbon
    {
        $date = Carbon::parse($from);

        return match ($frequency) {
            'quarterly' => $date->copy()->addMonthsNoOverflow(3),
            'yearly' => $date->copy()->addYearsNoOverflow(1),
            default => $date->copy()->addMonthNoOverflow(),
        };
    }
}
