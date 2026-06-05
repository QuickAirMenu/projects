<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'service_type_id',
        'description',
        'quantity',
        'unit_price',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
