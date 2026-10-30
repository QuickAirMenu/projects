<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'quote_id',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
