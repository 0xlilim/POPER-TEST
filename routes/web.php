<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\WelcomeController;

Route::get('/error', function () {
    Log::error('ERR Test', [
        'trace_id' => request()->header('X-Amzn-Trace-Id', 'N/A'),
    ]);
    abort(500, 'This is a simulated error on the /error route.');
});

Route::get('/health', [HealthCheckController::class, 'check']);
Route::get('/', [WelcomeController::class, 'index']);