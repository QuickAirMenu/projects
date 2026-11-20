<?php

namespace App\Models;

use App\Models\Concerns\ScopedToAccessibleProjects;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use ScopedToAccessibleProjects;

    protected $fillable = [
        'project_id', 'task_id', 'user_id', 'work_date', 'hours', 'description',
        'billable', 'hourly_rate', 'invoiced_invoice_id', 'created_by',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'billable' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiced_invoice_id');
    }

    public function scopeUnbilled($query)
    {
        return $query->where('billable', true)->whereNull('invoiced_invoice_id');
    }

    public function isInvoiced(): bool
    {
        return $this->invoiced_invoice_id !== null;
    }

    public function amount(): float
    {
        return round((float) $this->hours * (float) $this->hourly_rate, 2);
    }
}
