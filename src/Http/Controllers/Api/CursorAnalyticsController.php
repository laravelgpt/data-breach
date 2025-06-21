<?php

namespace LaravelGPT\DataBreach\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelGPT\DataBreach\Services\CursorAnalyticsService;

class CursorAnalyticsController
{
    public function __construct(
        private CursorAnalyticsService $cursorAnalyticsService
    ) {}

    /**
     * Track a cursor event.
     */
    public function trackEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'x' => 'required|integer|min:0',
            'y' => 'required|integer|min:0',
            'event_type' => 'required|string|in:move,click,hover,scroll',
            'element_id' => 'nullable|string',
            'element_type' => 'nullable|string',
            'velocity' => 'nullable|numeric|min:0',
        ]);

        $this->cursorAnalyticsService->trackCursorEvent($request, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Cursor event tracked successfully',
        ]);
    }

    /**
     * Get cursor analytics for a session.
     */
    public function getAnalytics(string $sessionKey): JsonResponse
    {
        $analytics = $this->cursorAnalyticsService->getSessionAnalytics($sessionKey);

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get cursor events for a session.
     */
    public function getEvents(string $sessionKey): JsonResponse
    {
        $events = $this->cursorAnalyticsService->getSessionEvents($sessionKey);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Archive old cursor data.
     */
    public function archiveData(): JsonResponse
    {
        $this->cursorAnalyticsService->archiveOldData();

        return response()->json([
            'success' => true,
            'message' => 'Cursor data archived successfully',
        ]);
    }

    /**
     * Enrich bug report with cursor data.
     */
    public function enrichBugReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => 'required|string',
            'bug_data' => 'required|array',
        ]);

        $enrichedData = $this->cursorAnalyticsService->enrichBugReport(
            $validated['session_key'],
            $validated['bug_data']
        );

        return response()->json([
            'success' => true,
            'data' => $enrichedData,
        ]);
    }
} 