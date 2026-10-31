<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    protected $fillable = [
        'prefix',
        'year_month',
        'last_number',
    ];

    protected $casts = [
        'last_number' => 'integer',
    ];

    public static function generate(string $prefix): string
    {
        $yearMonth = now()->format('Ym');

        $sequence = static::where('prefix', $prefix)
            ->where('year_month', $yearMonth)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            $sequence = static::create([
                'prefix' => $prefix,
                'year_month' => $yearMonth,
                'last_number' => 0,
            ]);
        }

        $sequence->increment('last_number');
        $number = $sequence->last_number;

        return $prefix.'-'.$yearMonth.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
