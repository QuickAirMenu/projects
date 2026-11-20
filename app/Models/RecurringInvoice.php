<?php

namespace App\Models;

use App\Models\Concerns\ScopedToAccessibleProjects;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RecurringInvoice extends Model
{
    use ScopedToAccessibleProjects;

    protected $fillable = [
        'client_id', 'project_id', 'name', 'invoice_type', 'items', 'tax_percentage',
        'discount_type', 'discount_amount', 'notes', 'terms', 'frequency',
        'next_run_date', 'last_run_at', 'is_active', 'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'next_run_date' => 'date',
        'last_run_at' => 'datetime',
        'is_active' => 'boolean',
        'tax_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)->whereDate('next_run_date', '<=', now()->toDateString());
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
