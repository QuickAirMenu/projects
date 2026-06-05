<?php
// app/Models/Contract.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'project_id',
        'client_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'contract_value',
        'payment_terms',
        'deliverables',
        'terms_and_conditions',
        'signed_by_client',
        'signed_at',
        'contract_file_path',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
        'signed_by_client' => 'boolean',
        'signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (!$contract->contract_number) {
                $lastContract = self::withTrashed()->latest('id')->first();
                $lastNumber = $lastContract ? (int) substr($lastContract->contract_number, -4) : 0;
                $contract->contract_number = generate_number(
                    workspace_config('contract.number_prefix', 'CNT'),
                    $lastNumber
                );
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
