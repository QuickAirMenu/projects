<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectWorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'step_name',
        'step_order',
        'completed',
        'completed_by',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
