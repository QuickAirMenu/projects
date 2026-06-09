<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_number',
        'project_id',
        'client_id',
        'quote_id',
        'invoice_type',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'discount_amount',
        'total',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'notes',
        'terms',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_number', 'total', 'payment_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $lastInvoice = self::withTrashed()->latest('id')->first();
                $lastNumber = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) : 0;
                $invoice->invoice_number = generate_number(
                    workspace_config('invoice.number_prefix', 'INV'),
                    $lastNumber
                );
            }

            // Calculate totals
            $invoice->tax_amount = ($invoice->subtotal * $invoice->tax_percentage) / 100;
            $invoice->total = $invoice->subtotal + $invoice->tax_amount - $invoice->discount_amount;
            $invoice->remaining_amount = $invoice->total;
        });

        static::updating(function (Invoice $invoice) {
            $subtotal = $invoice->items->sum('total');
            $invoice->subtotal = $subtotal;
            $invoice->tax_amount = $invoice->tax_percentage > 0 ? ($subtotal * $invoice->tax_percentage) / 100 : ($invoice->tax_amount ?? 0);
            $invoice->total = $subtotal + $invoice->tax_amount - ($invoice->discount_amount ?? 0);
            $invoice->remaining_amount = $invoice->total;
        });
    }

    /**
     * Relationships
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    /**
     * Scopes
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now());
    }

    /**
     * Accessors
     */
    public function getIsOverdueAttribute()
    {
        return $this->payment_status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    public function getPaymentStatusBadgeAttribute()
    {
        return payment_status_badge($this->payment_status);
    }
}
