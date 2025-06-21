<?php

namespace LaravelGPT\DataBreach\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class DarkWebMonitorService
{
    /**
     * Search for email/domain on dark web.
     */
    public function searchDarkWeb(string $query, string $type = 'email'): array
    {
        $queryHash = hash('sha256', $query . $type);
        $cacheKey = "dark_web_{$queryHash}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $results = [
            'query' => $query,
            'type' => $type,
            'found' => false,
            'breaches' => [],
            'total_breaches' => 0,
            'last_breach_date' => null,
            'sources' => [],
        ];

        // Search DeHashed
        $dehashedResult = $this->searchDeHashed($query, $type);
        if ($dehashedResult['found']) {
            $results['found'] = true;
            $results['breaches'] = array_merge($results['breaches'], $dehashedResult['breaches']);
            $results['total_breaches'] += $dehashedResult['total'];
            $results['sources'][] = 'DeHashed';
        }

        // Search GhostProject
        $ghostResult = $this->searchGhostProject($query, $type);
        if ($ghostResult['found']) {
            $results['found'] = true;
            $results['breaches'] = array_merge($results['breaches'], $ghostResult['breaches']);
            $results['total_breaches'] += $ghostResult['total'];
            $results['sources'][] = 'GhostProject';
        }

        // Calculate last breach date
        if (!empty($results['breaches'])) {
            $dates = array_column($results['breaches'], 'date');
            $results['last_breach_date'] = max($dates);
        }

        // Cache the result
        $ttl = Config::get('data-breach.cache.ttl.dark_web', 86400);
        Cache::put($cacheKey, $results, $ttl);

        return $results;
    }

    /**
     * Search DeHashed for dark web data.
     */
    protected function searchDeHashed(string $query, string $type): array
    {
        $apiKey = Config::get('data-breach.apis.dehashed');
        
        if (!$apiKey) {
            return ['found' => false, 'total' => 0, 'breaches' => []];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'User-Agent' => 'LaravelGPT-DataBreach/1.0',
            ])->get('https://api.dehashed.com/search', [
                'query' => $query,
                'size' => Config::get('data-breach.dark_web.max_results', 100),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $entries = $data['entries'] ?? [];
                $total = $data['total'] ?? 0;
                
                $breaches = [];
                foreach ($entries as $entry) {
                    $breaches[] = [
                        'source' => 'DeHashed',
                        'email' => $entry['email'] ?? null,
                        'password' => $entry['password'] ?? null,
                        'hash' => $entry['hash'] ?? null,
                        'database' => $entry['database_name'] ?? null,
                        'date' => $entry['date'] ?? null,
                        'line' => $entry['line'] ?? null,
                    ];
                }

                return [
                    'found' => $total > 0,
                    'total' => $total,
                    'breaches' => $breaches,
                ];
            }
        } catch (\Exception $e) {
            Log::error('DeHashed dark web search error', ['error' => $e->getMessage()]);
        }

        return ['found' => false, 'total' => 0, 'breaches' => []];
    }

    /**
     * Search GhostProject for dark web data.
     */
    protected function searchGhostProject(string $query, string $type): array
    {
        $apiKey = Config::get('data-breach.apis.ghostproject');
        
        if (!$apiKey) {
            return ['found' => false, 'total' => 0, 'breaches' => []];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'User-Agent' => 'LaravelGPT-DataBreach/1.0',
            ])->get('https://ghostproject.fr/api/v1/search', [
                'q' => $query,
                'limit' => Config::get('data-breach.dark_web.max_results', 100),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $entries = $data['data'] ?? [];
                $total = count($entries);
                
                $breaches = [];
                foreach ($entries as $entry) {
                    $breaches[] = [
                        'source' => 'GhostProject',
                        'email' => $entry['email'] ?? null,
                        'password' => $entry['password'] ?? null,
                        'hash' => $entry['hash'] ?? null,
                        'database' => $entry['source'] ?? null,
                        'date' => $entry['date'] ?? null,
                        'line' => $entry['line'] ?? null,
                    ];
                }

                return [
                    'found' => $total > 0,
                    'total' => $total,
                    'breaches' => $breaches,
                ];
            }
        } catch (\Exception $e) {
            Log::error('GhostProject dark web search error', ['error' => $e->getMessage()]);
        }

        return ['found' => false, 'total' => 0, 'breaches' => []];
    }
} 