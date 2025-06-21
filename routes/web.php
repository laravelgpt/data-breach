<?php

use Illuminate\Support\Facades\Route;
use LaravelGPT\DataBreach\Http\Controllers\Api\PasswordController;
use LaravelGPT\DataBreach\Http\Controllers\Api\IpController;
use LaravelGPT\DataBreach\Http\Controllers\Api\MalwareController;
use LaravelGPT\DataBreach\Http\Controllers\Api\DarkWebController;
use LaravelGPT\DataBreach\Http\Controllers\Api\CursorAnalyticsController;
use LaravelGPT\DataBreach\Http\Controllers\Api\SessionReplayController;

/*
|--------------------------------------------------------------------------
| Data Breach Web Routes
|--------------------------------------------------------------------------
|
| These routes are for the web interface of the data breach package.
| They provide access to the security tools and analytics dashboard.
|
*/

Route::prefix('data-breach')->name('data-breach.')->group(function () {
    
    // Dashboard
    Route::get('/', function () {
        return view('data-breach::dashboard');
    })->name('dashboard');

    // Password Security
    Route::get('/password-checker', function () {
        return view('data-breach::password-checker');
    })->name('password-checker');

    // IP Reputation
    Route::get('/ip-checker', function () {
        return view('data-breach::ip-checker');
    })->name('ip-checker');

    // Malware Scanner
    Route::get('/malware-scanner', function () {
        return view('data-breach::malware-scanner');
    })->name('malware-scanner');

    // Dark Web Monitor
    Route::get('/dark-web-monitor', function () {
        return view('data-breach::dark-web-monitor');
    })->name('dark-web-monitor');

    // Cursor Analytics
    Route::get('/cursor-analytics', function () {
        return view('data-breach::cursor-analytics');
    })->name('cursor-analytics');

    // Session Replay
    Route::get('/session-replay', function () {
        return view('data-breach::session-replay');
    })->name('session-replay');

    // API endpoints (web accessible)
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/password/check', [PasswordController::class, 'check'])->name('password.check');
        Route::post('/ip/check', [IpController::class, 'check'])->name('ip.check');
        Route::post('/malware/scan', [MalwareController::class, 'scan'])->name('malware.scan');
        Route::post('/dark-web/search', [DarkWebController::class, 'search'])->name('dark-web.search');
        Route::post('/cursor/track', [CursorAnalyticsController::class, 'track'])->name('cursor.track');
        Route::post('/session/record', [SessionReplayController::class, 'record'])->name('session.record');
    });
}); 