<?php

use Illuminate\Support\Facades\Route;
use LaravelGPT\DataBreach\Http\Controllers\Api\PasswordController;
use LaravelGPT\DataBreach\Http\Controllers\Api\IpController;
use LaravelGPT\DataBreach\Http\Controllers\Api\MalwareController;
use LaravelGPT\DataBreach\Http\Controllers\Api\DarkWebController;
use LaravelGPT\DataBreach\Http\Controllers\Api\PasskeyController;
use LaravelGPT\DataBreach\Http\Controllers\Api\CursorAnalyticsController;
use LaravelGPT\DataBreach\Http\Controllers\Api\SessionReplayController;

Route::prefix('data-breach')->name('data-breach.')->group(function () {
    
    // Password breach check
    Route::post('/password/check', [PasswordController::class, 'check'])
        ->name('password.check')
        ->middleware('throttle:60,1'); // 60 requests per minute

    // IP reputation check
    Route::post('/ip/check', [IpController::class, 'check'])
        ->name('ip.check')
        ->middleware('throttle:120,1'); // 120 requests per minute

    // File malware scan
    Route::post('/file/scan', [MalwareController::class, 'scanFile'])
        ->name('file.scan')
        ->middleware('throttle:10,1'); // 10 requests per minute

    // URL malware scan
    Route::post('/url/scan', [MalwareController::class, 'scanUrl'])
        ->name('url.scan')
        ->middleware('throttle:30,1'); // 30 requests per minute

    // Dark web search
    Route::get('/dark-web/search', [DarkWebController::class, 'search'])
        ->name('dark-web.search')
        ->middleware('throttle:30,1'); // 30 requests per minute

    // Email monitoring
    Route::post('/dark-web/monitor/email', [DarkWebController::class, 'monitorEmail'])
        ->name('dark-web.monitor.email')
        ->middleware('throttle:10,1'); // 10 requests per minute

    // Domain monitoring
    Route::post('/dark-web/monitor/domain', [DarkWebController::class, 'monitorDomain'])
        ->name('dark-web.monitor.domain')
        ->middleware('throttle:10,1'); // 10 requests per minute

    // Passkey generation
    Route::post('/generate/passkey', [PasskeyController::class, 'generatePasskey'])
        ->name('generate.passkey')
        ->middleware('throttle:60,1'); // 60 requests per minute

    // Passphrase generation
    Route::post('/generate/passphrase', [PasskeyController::class, 'generatePassphrase'])
        ->name('generate.passphrase')
        ->middleware('throttle:60,1'); // 60 requests per minute

    // PIN generation
    Route::post('/generate/pin', [PasskeyController::class, 'generatePin'])
        ->name('generate.pin')
        ->middleware('throttle:60,1'); // 60 requests per minute

    // Backup codes generation
    Route::post('/generate/backup-codes', [PasskeyController::class, 'generateBackupCodes'])
        ->name('generate.backup-codes')
        ->middleware('throttle:10,1'); // 10 requests per minute

    // 2FA recommendations
    Route::get('/2fa/recommendations', [PasskeyController::class, 'get2FARecommendations'])
        ->name('2fa.recommendations')
        ->middleware('throttle:60,1'); // 60 requests per minute

    // Cursor Analytics endpoints
    Route::prefix('cursor')->name('cursor.')->group(function () {
        // Track cursor event
        Route::post('/track', [CursorAnalyticsController::class, 'trackEvent'])
            ->name('track')
            ->middleware('throttle:1000,1'); // 1000 events per minute

        // Get session analytics
        Route::get('/analytics/{sessionKey}', [CursorAnalyticsController::class, 'getAnalytics'])
            ->name('analytics')
            ->middleware('throttle:60,1'); // 60 requests per minute

        // Get session events
        Route::get('/events/{sessionKey}', [CursorAnalyticsController::class, 'getEvents'])
            ->name('events')
            ->middleware('throttle:60,1'); // 60 requests per minute

        // Archive old data
        Route::post('/archive', [CursorAnalyticsController::class, 'archiveData'])
            ->name('archive')
            ->middleware('throttle:10,1'); // 10 requests per minute
    });

    // Session Replay endpoints
    Route::prefix('session')->name('session.')->group(function () {
        // Start recording
        Route::post('/start', [SessionReplayController::class, 'startRecording'])
            ->name('start')
            ->middleware('throttle:10,1'); // 10 requests per minute

        // Record event
        Route::post('/record', [SessionReplayController::class, 'recordEvent'])
            ->name('record')
            ->middleware('throttle:500,1'); // 500 events per minute

        // Stop recording
        Route::post('/stop', [SessionReplayController::class, 'stopRecording'])
            ->name('stop')
            ->middleware('throttle:10,1'); // 10 requests per minute

        // Get replay data
        Route::get('/replay/{sessionKey}', [SessionReplayController::class, 'getReplay'])
            ->name('replay')
            ->middleware('throttle:60,1'); // 60 requests per minute

        // Get analytics
        Route::get('/analytics/{sessionKey}', [SessionReplayController::class, 'getAnalytics'])
            ->name('analytics')
            ->middleware('throttle:60,1'); // 60 requests per minute

        // Get user sessions
        Route::get('/user/{userId}', [SessionReplayController::class, 'getUserSessions'])
            ->name('user.sessions')
            ->middleware('throttle:30,1'); // 30 requests per minute

        // Get tenant sessions
        Route::get('/tenant/{tenantId}', [SessionReplayController::class, 'getTenantSessions'])
            ->name('tenant.sessions')
            ->middleware('throttle:30,1'); // 30 requests per minute

        // Cleanup old sessions
        Route::post('/cleanup', [SessionReplayController::class, 'cleanup'])
            ->name('cleanup')
            ->middleware('throttle:5,1'); // 5 requests per minute
    });

    // Bug Report enrichment
    Route::post('/bug-report/enrich', [CursorAnalyticsController::class, 'enrichBugReport'])
        ->name('bug-report.enrich')
        ->middleware('throttle:30,1'); // 30 requests per minute
}); 