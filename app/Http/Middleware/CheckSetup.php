<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckSetup
{
    public function handle(Request $request, Closure $next)
    {
        // Allow these routes without setup check
        $allowed = ['setup/*', 'health', 'backup-trigger*', 'login', 'logout'];
        foreach ($allowed as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        // If no users exist, show pending page
        if (User::count() === 0) {
            return response()->view('setup.pending', [], 503);
        }

        return $next($request);
    }
}
