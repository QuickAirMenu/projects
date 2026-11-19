<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankImport extends Model
{
    protected $fillable = ['account_id', 'file_name', 'row_count', 'matched_count', 'created_count', 'created_by'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
