<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Quote extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'quote_number',
        'public_token',
        'client_id',
        'subject',
        'description',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'total',
        'status',
        'valid_until',
        'sent_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'converted_to_project_id',
        'notes',
        'terms_and_conditions',
        'created_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['quote_number', 'status', 'total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (!$quote->quote_number) {
                $lastQuote = self::withTrashed()->latest('id')->first();
                $lastNumber = $lastQuote ? (int) substr($lastQuote->quote_number, -4) : 0;
                $quote->quote_number = generate_number(
                    workspace_config('quote.number_prefix', 'QT'),
                    $lastNumber
                );
            }

            if (!$quote->public_token) {
                $quote->public_token = Str::random(10);
            }

            // Set default valid until date
            if (!$quote->valid_until) {
                $quote->valid_until = now()->addDays(workspace_config('quote.valid_days', 30));
            }

            // Calculate totals
            $quote->tax_amount = ($quote->subtotal * $quote->tax_percentage) / 100;
            $quote->total = $quote->subtotal + $quote->tax_amount - $quote->discount_amount;
        });

        static::updating(function (Quote $quote) {
            $subtotal = $quote->items->sum('total');
            $quote->subtotal = $subtotal;
            $quote->tax_amount = $quote->tax_percentage > 0 ? ($subtotal * $quote->tax_percentage) / 100 : ($quote->tax_amount ?? 0);
            $quote->total = $subtotal + $quote->tax_amount - ($quote->discount_amount ?? 0);
        });
    }

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator()
    {
        return $this->createdBy();
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'quote_id');
    }

    public function convertedProject()
    {
        return $this->belongsTo(Project::class, 'converted_to_project_id');
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now())
            ->where('status', '!=', 'approved');
    }

    /**
     * Accessors
     */
    public function getIsExpiredAttribute()
    {
        return $this->status !== 'approved' && $this->valid_until && $this->valid_until->isPast();
    }

    public function getIsConvertedAttribute()
    {
        return $this->converted_to_project_id !== null;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => ['color' => 'secondary', 'text' => __('finance.quote_status.draft')],
            'sent' => ['color' => 'info', 'text' => __('finance.quote_status.sent')],
            'approved' => ['color' => 'success', 'text' => __('finance.quote_status.approved')],
            'rejected' => ['color' => 'danger', 'text' => __('finance.quote_status.rejected')],
        ];

        $badge = $badges[$this->status] ?? $badges['draft'];
        return status_badge($badge['text'], $badge['color']);
    }
}
