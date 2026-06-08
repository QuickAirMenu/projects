<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_id',
        'payment_number',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'notes',
        'proof_file_name',
        'proof_file_path',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['payment_number', 'amount', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                $lastPayment = self::withTrashed()->latest('id')->first();
                $lastNumber = $lastPayment ? (int) substr($lastPayment->payment_number, -4) : 0;
                $payment->payment_number = generate_number(
                    workspace_config('payment.number_prefix', 'PAY'),
                    $lastNumber
                );
            }
        });

        // Update invoice after payment is created
        static::created(function ($payment) {
            $invoice = $payment->invoice;
            $invoice->paid_amount += $payment->amount;
            $invoice->remaining_amount = $invoice->total - $invoice->paid_amount;
            
            // Update payment status
            if ($invoice->remaining_amount <= 0) {
                $invoice->payment_status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->payment_status = 'partially_paid';
            }
            
            $invoice->save();
        });

        static::deleted(function (Payment $payment) {
            if ($payment->invoice) {
                $paid = $payment->invoice->payments()->sum('amount');
                $payment->invoice->update([
                    'paid_amount' => $paid,
                    'remaining_amount' => $payment->invoice->total - $paid,
                ]);
            }
        });

        static::restored(function (Payment $payment) {
            if ($payment->invoice) {
                $paid = $payment->invoice->payments()->sum('amount');
                $payment->invoice->update([
                    'paid_amount' => $paid,
                    'remaining_amount' => $payment->invoice->total - $paid,
                ]);
            }
        });
    }

    /**
     * Relationships
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function receiver()
    {
        return $this->receivedBy();
    }

    /**
     * Accessors
     */
    public function getPaymentMethodTextAttribute()
    {
        $methods = workspace_config('payment.methods', []);
        return $methods[$this->payment_method] ?? $this->payment_method;
    }
}
