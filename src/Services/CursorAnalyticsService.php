<?php

namespace LaravelGPT\DataBreach\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CursorAnalyticsService
{
    /**
     * Track cursor movement event.
     */
    public function trackCursorEvent(Request $request, array $eventData): void
    {
        if (!Config::get('data-breach.cursor.tracking_enabled', true)) {
            return;
        }

        try {
            $sessionKey = $request->session()->getId();
            $userId = $request->user()?->id;
            $tenantId = $request->user()?->tenant_id;

            $event = [
                'session_key' => $sessionKey,
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'x' => $eventData['x'] ?? 0,
                'y' => $eventData['y'] ?? 0,
                'event_type' => $eventData['event_type'] ?? 'move',
                'element_id' => $eventData['element_id'] ?? null,
                'element_type' => $eventData['element_type'] ?? null,
                'timestamp' => now(),
                'user_agent' => $request->userAgent(),
                'device_type' => $this->detectDeviceType($request),
                'velocity' => $eventData['velocity'] ?? 0,
                'page_url' => $request->url(),
                'referrer' => $request->referer(),
            ];

            // Store in cache for real-time access
            $this->storeCursorEvent($event);

            // Log for analytics
            if (Config::get('data-breach.cursor.analytics_enabled', true)) {
                $this->logCursorAnalytics($event);
            }

            // AI UX feedback analysis
            if (Config::get('data-breach.cursor.ai_ux_feedback', true)) {
                $this->analyzeCursorPath($event);
            }

        } catch (\Exception $e) {
            Log::error('Cursor tracking error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Store cursor event in cache for real-time access.
     */
    protected function storeCursorEvent(array $event): void
    {
        $sessionKey = $event['session_key'];
        $cacheKey = "cursor_session_{$sessionKey}";
        
        $sessionEvents = Cache::get($cacheKey, []);
        $sessionEvents[] = $event;
        
        // Keep only last 100 events per session
        if (count($sessionEvents) > 100) {
            $sessionEvents = array_slice($sessionEvents, -100);
        }
        
        Cache::put($cacheKey, $sessionEvents, 3600); // 1 hour TTL
    }

    /**
     * Log cursor analytics for long-term analysis.
     */
    protected function logCursorAnalytics(array $event): void
    {
        Log::channel('cursor_analytics')->info('Cursor event', $event);
    }

    /**
     * Analyze cursor path for UX feedback.
     */
    protected function analyzeCursorPath(array $event): void
    {
        $sessionKey = $event['session_key'];
        $cacheKey = "cursor_analysis_{$sessionKey}";
        
        $analysis = Cache::get($cacheKey, [
            'total_moves' => 0,
            'clicks' => 0,
            'hover_elements' => [],
            'fast_movements' => 0,
            'slow_movements' => 0,
        ]);

        $analysis['total_moves']++;
        
        if ($event['event_type'] === 'click') {
            $analysis['clicks']++;
        }
        
        if ($event['event_type'] === 'hover' && $event['element_id']) {
            $analysis['hover_elements'][$event['element_id']] = 
                ($analysis['hover_elements'][$event['element_id']] ?? 0) + 1;
        }
        
        if ($event['velocity'] > 100) {
            $analysis['fast_movements']++;
        } elseif ($event['velocity'] < 10) {
            $analysis['slow_movements']++;
        }
        
        Cache::put($cacheKey, $analysis, 3600);
    }

    /**
     * Get cursor analytics for a session.
     */
    public function getSessionAnalytics(string $sessionKey): array
    {
        $cacheKey = "cursor_analysis_{$sessionKey}";
        return Cache::get($cacheKey, []);
    }

    /**
     * Get real-time cursor events for a session.
     */
    public function getSessionEvents(string $sessionKey): array
    {
        $cacheKey = "cursor_session_{$sessionKey}";
        return Cache::get($cacheKey, []);
    }

    /**
     * Detect device type from user agent.
     */
    protected function detectDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Archive old cursor data based on policy.
     */
    public function archiveOldData(): void
    {
        $archiveDays = Config::get('data-breach.cursor.archive_policy_days', 30);
        $cutoffDate = now()->subDays($archiveDays);
        
        // This would typically archive to a separate storage
        // For now, we'll just log the archive action
        Log::info('Cursor data archive completed', [
            'cutoff_date' => $cutoffDate,
            'archive_days' => $archiveDays,
        ]);
    }

    /**
     * Enrich bug reports with cursor data.
     */
    public function enrichBugReport(string $sessionKey, array $bugData): array
    {
        if (!Config::get('data-breach.cursor.bug_report_enrichment', true)) {
            return $bugData;
        }

        $cursorEvents = $this->getSessionEvents($sessionKey);
        $analytics = $this->getSessionAnalytics($sessionKey);

        return array_merge($bugData, [
            'cursor_events_count' => count($cursorEvents),
            'cursor_analytics' => $analytics,
            'last_cursor_position' => end($cursorEvents) ?: null,
            'session_duration' => $this->calculateSessionDuration($cursorEvents),
        ]);
    }

    /**
     * Calculate session duration from cursor events.
     */
    protected function calculateSessionDuration(array $events): ?int
    {
        if (empty($events)) {
            return null;
        }

        $firstEvent = reset($events);
        $lastEvent = end($events);

        return $lastEvent['timestamp']->diffInSeconds($firstEvent['timestamp']);
    }
} 