<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'whatsapp',
        'city',
        'country',
        'address',
        'status',
        'industry',
        'source',
        'notes',
        'lifetime_value',
        'assigned_to',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'lifetime_value' => 'decimal:2',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'company_name', 'email', 'phone', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedUser()
    {
        return $this->assignedTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function portalAccount()
    {
        return $this->hasOne(ClientPortalAccount::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function communications()
    {
        return $this->hasMany(ClientCommunication::class);
    }

    /**
     * Scopes
     */
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVip($query)
    {
        return $query->where('status', 'vip');
    }

    public function scopePotential($query)
    {
        return $query->where('status', 'potential');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accessors
     */
    
    public function getFullNameAttribute()
    {
        return $this->company_name ?: $this->name;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => ['color' => 'success', 'text' => __('common.status.active')],
            'potential' => ['color' => 'info', 'text' => __('common.status.potential')],
            'inactive' => ['color' => 'secondary', 'text' => __('common.status.inactive')],
            'vip' => ['color' => 'warning', 'text' => __('common.status.vip')],
        ];

        $badge = $badges[$this->status] ?? $badges['potential'];
        return status_badge($badge['text'], $badge['color']);
    }
}
