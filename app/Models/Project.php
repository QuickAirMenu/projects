<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Project extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_number',
        'name',
        'client_id',
        'service_type_id',
        'description',
        'start_date',
        'deadline',
        'delivered_at',
        'priority',
        'status_id',
        'manager_id',
        'budget',
        'actual_cost',
        'payment_status',
        'progress_percentage',
        'internal_notes',
        'client_notes',
        'is_archived',
        'archived_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'delivered_at' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'progress_percentage' => 'integer',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status_id', 'priority', 'progress_percentage', 'payment_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Generate project number on creating
        static::creating(function ($project) {
            if (!$project->project_number) {
                $lastProject = self::withTrashed()->latest('id')->first();
                $lastNumber = $lastProject ? (int) substr($lastProject->project_number, -4) : 0;
                $project->project_number = generate_number(
                    workspace_config('project.number_prefix', 'PRJ'),
                    $lastNumber
                );
            }
        });
    }

    /**
     * Relationships
     */
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Team members
    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role', 'assigned_at')
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class);
    }

    public function workflowSteps()
    {
        return $this->hasMany(ProjectWorkflowStep::class)->orderBy('step_order');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'converted_to_project_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class);
    }

    /**
     * Scopes
     */
    
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
            ->whereNotIn('status_id', function($q) {
                $q->select('id')
                    ->from('project_statuses')
                    ->whereIn('slug', ['completed', 'closed', 'cancelled']);
            });
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    /**
     * Accessors
     */
    
    public function getIsOverdueAttribute()
    {
        if (!$this->deadline) {
            return false;
        }

        $completedStatuses = ['completed', 'closed', 'cancelled'];
        return $this->deadline->isPast() && !in_array($this->status->slug, $completedStatuses);
    }

    public function getPriorityBadgeAttribute()
    {
        return priority_badge($this->priority);
    }

    public function getPaymentStatusBadgeAttribute()
    {
        return payment_status_badge($this->payment_status);
    }

    public function getRemainingBudgetAttribute()
    {
        return $this->budget - $this->actual_cost;
    }

    public function getCompletedTasksCountAttribute()
    {
        return $this->tasks()->whereHas('status', function($q) {
            $q->where('slug', 'done');
        })->count();
    }

    public function getTotalTasksCountAttribute()
    {
        return $this->tasks()->count();
    }
}
