<?php

namespace LaravelGPT\DataBreach\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelGPT\DataBreach\Services\SessionReplayService;

class SessionReplayController
{
    public function __construct(
        private SessionReplayService $sessionReplayService
    ) {}

    /**
     * Start recording a session.
     */
    public function startRecording(Request $request): JsonResponse
    {
        $sessionKey = $this->sessionReplayService->startRecording($request);

        return response()->json([
            'success' => true,
            'session_key' => $sessionKey,
            'message' => 'Session recording started',
        ]);
    }

    /**
     * Record a session event.
     */
    public function recordEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => 'required|string',
            'type' => 'required|string|in:page_load,click,scroll,form_input,form_submit,error',
            'data' => 'required|array',
            'screenshot' => 'nullable|string',
        ]);

        $this->sessionReplayService->recordEvent(
            $validated['session_key'],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Session event recorded',
        ]);
    }

    /**
     * Stop recording a session.
     */
    public function stopRecording(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => 'required|string',
        ]);

        $sessionData = $this->sessionReplayService->stopRecording($validated['session_key']);

        return response()->json([
            'success' => true,
            'data' => $sessionData,
            'message' => 'Session recording stopped',
        ]);
    }

    /**
     * Get session replay data.
     */
    public function getReplay(string $sessionKey): JsonResponse
    {
        $replayData = $this->sessionReplayService->getSessionReplay($sessionKey);

        if (!$replayData) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $replayData,
        ]);
    }

    /**
     * Get session analytics.
     */
    public function getAnalytics(string $sessionKey): JsonResponse
    {
        $analytics = $this->sessionReplayService->getSessionAnalytics($sessionKey);

        if (empty($analytics)) {
            return response()->json([
                'success' => false,
                'message' => 'Session analytics not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get sessions for a user.
     */
    public function getUserSessions(int $userId): JsonResponse
    {
        $sessions = $this->sessionReplayService->getUserSessions($userId);

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Get sessions for a tenant.
     */
    public function getTenantSessions(int $tenantId): JsonResponse
    {
        $sessions = $this->sessionReplayService->getTenantSessions($tenantId);

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Cleanup old sessions.
     */
    public function cleanup(): JsonResponse
    {
        $this->sessionReplayService->cleanupOldSessions();

        return response()->json([
            'success' => true,
            'message' => 'Old sessions cleaned up successfully',
        ]);
    }
} 