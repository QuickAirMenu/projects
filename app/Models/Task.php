<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'parent_task_id',
        'title',
        'description',
        'status_id',
        'priority',
        'assigned_to',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'is_blocked',
        'blocked_reason',
        'sort_order',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'integer',
        'is_blocked' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status_id', 'priority', 'assigned_to', 'progress_percentage'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Parent task for subtasks
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    // Subtasks
    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('sort_order');
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Scopes
     */
    public function scopeMainTasks($query)
    {
        return $query->whereNull('parent_task_id');
    }

    public function scopeSubtasks($query)
    {
        return $query->whereNotNull('parent_task_id');
    }

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereHas('status', function ($q) {
                $q->where('slug', '!=', 'done');
            });
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasAnyRole(['super-admin', 'admin', 'finance'])) {
            return $query;
        }

        return $query->where('assigned_to', $user->id);
    }

    /**
     * Accessors
     */
    public function getIsOverdueAttribute()
    {
        if (! $this->due_date || $this->status->slug === 'done') {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function getPriorityBadgeAttribute()
    {
        return priority_badge($this->priority);
    }

    public function getCompletedChecklistsCountAttribute()
    {
        if ($this->relationLoaded('checklists')) {
            return $this->checklists->where('is_completed', true)->count();
        }

        return $this->checklists()->where('is_completed', true)->count();
    }

    public function getTotalChecklistsCountAttribute()
    {
        if ($this->relationLoaded('checklists')) {
            return $this->checklists->count();
        }

        return $this->checklists()->count();
    }

    public function getChecklistProgressAttribute()
    {
        $total = $this->total_checklists_count;
        if ($total === 0) {
            return 0;
        }

        return round(($this->completed_checklists_count / $total) * 100);
    }

    public function getTotalLoggedHoursAttribute()
    {
        if ($this->relationLoaded('timeLogs')) {
            return $this->timeLogs->sum('hours');
        }

        return $this->timeLogs()->sum('hours');
    }
}
