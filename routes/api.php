<?php

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [ApiController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [ApiController::class, 'logout']);
        Route::get('me', [ApiController::class, 'me']);
        Route::get('dashboard', [ApiController::class, 'dashboard']);
        Route::get('projects', [ApiController::class, 'projects']);
        Route::get('clients', [ApiController::class, 'clients']);
        Route::get('invoices', [ApiController::class, 'invoices']);
        Route::get('quotes', [ApiController::class, 'quotes']);
        Route::get('tasks', [ApiController::class, 'tasks']);
    });
});
