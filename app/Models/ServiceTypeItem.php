<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTypeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'description',
        'quantity',
        'unit_price',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
