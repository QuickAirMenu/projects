<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const JOB_LEVELS = [
        'intern' => 'Intern / Trainee',
        'junior' => 'Junior',
        'mid' => 'Mid-Level / Specialist',
        'senior' => 'Senior',
        'lead' => 'Lead',
        'manager' => 'Manager',
        'head' => 'Head / Director',
        'vp' => 'VP / Vice President',
        'clevel' => 'C-Level',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'job_title',
        'job_level',
        'department',
        'is_active',
        'language',
        'timezone',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'job_title', 'job_level', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */

    // Projects managed by this user
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    // Projects this user is a team member of
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role', 'assigned_at')
            ->withTimestamps();
    }

    // Tasks assigned to this user
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // Alias used by PerformanceReportController and DashboardService
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // Tasks created by this user
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    // Clients assigned to this user
    public function clients()
    {
        return $this->hasMany(Client::class, 'assigned_to');
    }

    // Time logs
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    // Comments
    public function taskComments()
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->role($role);
    }

    /**
     * Accessors & Mutators
     */
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? Storage::url($this->avatar) : user_avatar($this);
    }

    public function getIsAdminAttribute()
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }
}
