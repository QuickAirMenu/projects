<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'project_id',
        'requested_by',
        'title',
        'description',
        'scope_type',
        'impact_analysis',
        'additional_cost',
        'additional_hours',
        'status',
        'priority',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'additional_cost' => 'decimal:2',
        'additional_hours' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator()
    {
        return $this->createdBy();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => ['color' => 'warning', 'text' => __('change_request.status_pending')],
            'approved' => ['color' => 'success', 'text' => __('change_request.status_approved')],
            'rejected' => ['color' => 'danger', 'text' => __('change_request.status_rejected')],
            'implemented' => ['color' => 'info', 'text' => __('change_request.status_implemented')],
        ];

        $badge = $badges[$this->status] ?? $badges['pending'];

        return status_badge($badge['text'], $badge['color']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($changeRequest) {
            if (! $changeRequest->request_number) {
                $lastCR = self::latest('id')->first();
                $lastNumber = $lastCR ? (int) substr($lastCR->request_number, -4) : 0;
                $changeRequest->request_number = generate_number(
                    workspace_config('change_request.number_prefix', 'CR'),
                    $lastNumber
                );
            }
        });
    }
}
