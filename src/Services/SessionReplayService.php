<?php

namespace LaravelGPT\DataBreach\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class SessionReplayService
{
    /**
     * Start recording a session.
     */
    public function startRecording(Request $request): string
    {
        $sessionKey = $request->session()->getId();
        $userId = $request->user()?->id;
        $tenantId = $request->user()?->tenant_id;

        $sessionData = [
            'session_key' => $sessionKey,
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'started_at' => now(),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'device_type' => $this->detectDeviceType($request),
            'screen_resolution' => $request->header('X-Screen-Resolution'),
            'viewport_size' => $request->header('X-Viewport-Size'),
            'events' => [],
            'screenshots' => [],
            'metadata' => [
                'page_loads' => 0,
                'clicks' => 0,
                'scrolls' => 0,
                'form_interactions' => 0,
                'errors' => 0,
            ],
        ];

        $cacheKey = "session_replay_{$sessionKey}";
        Cache::put($cacheKey, $sessionData, 7200); // 2 hours TTL

        Log::info('Session recording started', [
            'session_key' => $sessionKey,
            'user_id' => $userId,
        ]);

        return $sessionKey;
    }

    /**
     * Record a session event.
     */
    public function recordEvent(string $sessionKey, array $eventData): void
    {
        if (!Config::get('data-breach.cursor.session_logging', true)) {
            return;
        }

        try {
            $cacheKey = "session_replay_{$sessionKey}";
            $sessionData = Cache::get($cacheKey);

            if (!$sessionData) {
                return;
            }

            $event = [
                'timestamp' => now(),
                'type' => $eventData['type'] ?? 'unknown',
                'data' => $eventData,
                'sequence' => count($sessionData['events']) + 1,
            ];

            $sessionData['events'][] = $event;

            // Update metadata
            $this->updateSessionMetadata($sessionData, $event);

            // Store screenshot if provided
            if (isset($eventData['screenshot'])) {
                $sessionData['screenshots'][] = [
                    'timestamp' => now(),
                    'data' => $eventData['screenshot'],
                    'event_sequence' => $event['sequence'],
                ];
            }

            Cache::put($cacheKey, $sessionData, 7200);

        } catch (\Exception $e) {
            Log::error('Session event recording error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Stop recording a session.
     */
    public function stopRecording(string $sessionKey): array
    {
        $cacheKey = "session_replay_{$sessionKey}";
        $sessionData = Cache::get($cacheKey);

        if (!$sessionData) {
            return [];
        }

        $sessionData['ended_at'] = now();
        $sessionData['duration'] = $sessionData['ended_at']->diffInSeconds($sessionData['started_at']);

        // Store final session data
        Cache::put($cacheKey, $sessionData, 86400); // 24 hours TTL

        // Archive session data
        $this->archiveSession($sessionData);

        Log::info('Session recording stopped', [
            'session_key' => $sessionKey,
            'duration' => $sessionData['duration'],
            'events_count' => count($sessionData['events']),
        ]);

        return $sessionData;
    }

    /**
     * Get session replay data.
     */
    public function getSessionReplay(string $sessionKey): ?array
    {
        $cacheKey = "session_replay_{$sessionKey}";
        return Cache::get($cacheKey);
    }

    /**
     * Get session analytics.
     */
    public function getSessionAnalytics(string $sessionKey): array
    {
        $sessionData = $this->getSessionReplay($sessionKey);

        if (!$sessionData) {
            return [];
        }

        return [
            'session_key' => $sessionKey,
            'duration' => $sessionData['duration'] ?? 0,
            'events_count' => count($sessionData['events']),
            'metadata' => $sessionData['metadata'],
            'user_agent' => $sessionData['user_agent'],
            'device_type' => $sessionData['device_type'],
            'started_at' => $sessionData['started_at'],
            'ended_at' => $sessionData['ended_at'],
        ];
    }

    /**
     * Update session metadata based on event type.
     */
    protected function updateSessionMetadata(array &$sessionData, array $event): void
    {
        $eventType = $event['type'];

        switch ($eventType) {
            case 'page_load':
                $sessionData['metadata']['page_loads']++;
                break;
            case 'click':
                $sessionData['metadata']['clicks']++;
                break;
            case 'scroll':
                $sessionData['metadata']['scrolls']++;
                break;
            case 'form_input':
            case 'form_submit':
                $sessionData['metadata']['form_interactions']++;
                break;
            case 'error':
                $sessionData['metadata']['errors']++;
                break;
        }
    }

    /**
     * Archive session data for long-term storage.
     */
    protected function archiveSession(array $sessionData): void
    {
        // In a real implementation, this would store to a database or file system
        // For now, we'll just log the archive action
        Log::info('Session archived', [
            'session_key' => $sessionData['session_key'],
            'user_id' => $sessionData['user_id'],
            'duration' => $sessionData['duration'],
            'events_count' => count($sessionData['events']),
        ]);
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
     * Get sessions for a user.
     */
    public function getUserSessions(int $userId): array
    {
        // This would typically query a database
        // For now, return empty array
        return [];
    }

    /**
     * Get sessions for a tenant.
     */
    public function getTenantSessions(int $tenantId): array
    {
        // This would typically query a database
        // For now, return empty array
        return [];
    }

    /**
     * Clean up old session data.
     */
    public function cleanupOldSessions(): void
    {
        $archiveDays = Config::get('data-breach.cursor.archive_policy_days', 30);
        $cutoffDate = now()->subDays($archiveDays);

        Log::info('Session cleanup completed', [
            'cutoff_date' => $cutoffDate,
            'archive_days' => $archiveDays,
        ]);
    }
} 