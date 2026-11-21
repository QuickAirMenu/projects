<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;

class EmailValidationController extends Controller
{
    public function check(Request $request, EmailVerificationService $service)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'max:255'],
        ]);

        return response()->json($service->inspect($data['email']));
    }
}
