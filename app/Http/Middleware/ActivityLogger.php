<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    /** Methods that modify data */
    protected array $logMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /** Route name patterns to skip */
    protected array $skipRoutes = ['login', 'logout', 'lang.switch', 'notifications.*'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->user() &&
            in_array($request->method(), $this->logMethods) &&
            ! $this->shouldSkip($request)
        ) {
            ActivityLog::create([
                'log_name' => 'http',
                'description' => $request->method().' '.$request->path(),
                'causer_type' => get_class($request->user()),
                'causer_id' => $request->user()->id,
                'properties' => json_encode([
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'status' => $response->getStatusCode(),
                ]),
            ]);
        }

        return $response;
    }

    protected function shouldSkip(Request $request): bool
    {
        foreach ($this->skipRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
