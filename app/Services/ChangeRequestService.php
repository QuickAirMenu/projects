<?php

namespace App\Services;

use App\Models\ChangeRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChangeRequestService
{
    public function create(array $data): ChangeRequest
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            $data['request_number'] = $this->generateRequestNumber();

            return ChangeRequest::create($data);
        });
    }

    protected function generateRequestNumber(): string
    {
        $prefix = 'CR-'.date('Ym').'-';
        $last = ChangeRequest::withTrashed()->where('request_number', 'like', $prefix.'%')
            ->orderByDesc('request_number')
            ->first();

        $seq = $last ? (int) substr($last->request_number, -4) + 1 : 1;

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
