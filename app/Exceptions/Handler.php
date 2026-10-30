<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {});
    }

    public function render($request, Throwable $e)
    {
        if ($this->shouldReturnCustomErrorView($request, $e)) {
            $status = $this->getStatusCodeForException($e);
            $view = $this->resolveErrorView($status);

            if ($view !== null) {
                return response()->view($view, [
                    'exception' => $e,
                    'status' => $status,
                ], $status);
            }
        }

        return parent::render($request, $e);
    }

    protected function shouldReturnCustomErrorView(Request $request, Throwable $e): bool
    {
        if (! $request->expectsJson() && ! $request->ajax() && ! app()->runningInConsole()) {
            if (config('app.debug') && $request->has('debug')) {
                return false;
            }

            return true;
        }

        return false;
    }

    protected function getStatusCodeForException(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        if ($e instanceof AuthorizationException) {
            return 403;
        }

        return 500;
    }

    protected function resolveErrorView(int $status): ?string
    {
        $map = [
            403 => 'errors.403',
            404 => 'errors.404',
            500 => 'errors.500',
        ];

        if (! array_key_exists($status, $map)) {
            $status = $status >= 500 ? 500 : 404;
        }

        $view = $map[$status] ?? null;
        if ($view === null) {
            return null;
        }

        return view()->exists($view) ? $view : null;
    }
}
