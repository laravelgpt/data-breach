<?php

namespace LaravelGPT\DataBreach\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class PasswordBreachService
{
    /**
     * Check if a password has been compromised.
     */
    public function checkPassword(string $password): array
    {
        $hash = sha1($password);
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        $cacheKey = "password_breach_{$hash}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $results = [
            'compromised' => false,
            'breach_count' => 0,
            'sources' => [],
            'strength' => $this->analyzePasswordStrength($password),
            'recommendations' => [],
        ];

        // Check HIBP
        $hibpResult = $this->checkHIBP($prefix, $suffix);
        if ($hibpResult['found']) {
            $results['compromised'] = true;
            $results['breach_count'] += $hibpResult['count'];
            $results['sources'][] = 'HIBP';
        }

        // Check DeHashed
        $dehashedResult = $this->checkDeHashed($password);
        if ($dehashedResult['found']) {
            $results['compromised'] = true;
            $results['breach_count'] += $dehashedResult['count'];
            $results['sources'][] = 'DeHashed';
        }

        // Check LeakCheck
        $leakcheckResult = $this->checkLeakCheck($password);
        if ($leakcheckResult['found']) {
            $results['compromised'] = true;
            $results['breach_count'] += $leakcheckResult['count'];
            $results['sources'][] = 'LeakCheck';
        }

        // Generate recommendations
        $results['recommendations'] = $this->generateRecommendations($results);

        // Cache the result
        $ttl = Config::get('data-breach.cache.ttl.password_check', 3600);
        Cache::put($cacheKey, $results, $ttl);

        // Log if compromised
        if ($results['compromised'] && Config::get('data-breach.logging.log_breaches', true)) {
            Log::warning('Password breach detected', [
                'breach_count' => $results['breach_count'],
                'sources' => $results['sources'],
                'strength' => $results['strength']['score'],
            ]);
        }

        return $results;
    }

    /**
     * Check password against Have I Been Pwned.
     */
    protected function checkHIBP(string $prefix, string $suffix): array
    {
        $apiKey = Config::get('data-breach.apis.hibp');
        
        if (!$apiKey) {
            return ['found' => false, 'count' => 0];
        }

        try {
            $response = Http::withHeaders([
                'hibp-api-key' => $apiKey,
                'user-agent' => 'LaravelGPT-DataBreach/1.0',
            ])->get("https://api.pwnedpasswords.com/range/{$prefix}");

            if ($response->successful()) {
                $lines = explode("\n", $response->body());
                
                foreach ($lines as $line) {
                    $parts = explode(':', $line);
                    if (count($parts) === 2 && strtoupper($parts[0]) === $suffix) {
                        return [
                            'found' => true,
                            'count' => (int) $parts[1],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('HIBP API error', ['error' => $e->getMessage()]);
        }

        return ['found' => false, 'count' => 0];
    }

    /**
     * Check password against DeHashed.
     */
    protected function checkDeHashed(string $password): array
    {
        $apiKey = Config::get('data-breach.apis.dehashed');
        
        if (!$apiKey) {
            return ['found' => false, 'count' => 0];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'User-Agent' => 'LaravelGPT-DataBreach/1.0',
            ])->get('https://api.dehashed.com/search', [
                'query' => $password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $count = $data['total'] ?? 0;
                
                return [
                    'found' => $count > 0,
                    'count' => $count,
                ];
            }
        } catch (\Exception $e) {
            Log::error('DeHashed API error', ['error' => $e->getMessage()]);
        }

        return ['found' => false, 'count' => 0];
    }

    /**
     * Check password against LeakCheck.
     */
    protected function checkLeakCheck(string $password): array
    {
        $apiKey = Config::get('data-breach.apis.leakcheck');
        
        if (!$apiKey) {
            return ['found' => false, 'count' => 0];
        }

        try {
            $response = Http::post('https://leakcheck.io/api/public', [
                'key' => $apiKey,
                'check' => $password,
                'type' => 'password',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $found = $data['found'] ?? false;
                $count = $data['count'] ?? 0;
                
                return [
                    'found' => $found,
                    'count' => $count,
                ];
            }
        } catch (\Exception $e) {
            Log::error('LeakCheck API error', ['error' => $e->getMessage()]);
        }

        return ['found' => false, 'count' => 0];
    }

    /**
     * Analyze password strength.
     */
    public function analyzePasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Length check
        if (strlen($password) >= 12) {
            $score += 2;
        } elseif (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Password should be at least 8 characters long';
        }

        // Character variety checks
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add uppercase letters';
        }

        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add lowercase letters';
        }

        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add numbers';
        }

        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add special characters';
        }

        // Common patterns check
        $commonPatterns = [
            'password', '123456', 'qwerty', 'admin', 'letmein',
            'welcome', 'monkey', 'dragon', 'master', 'football'
        ];

        foreach ($commonPatterns as $pattern) {
            if (stripos($password, $pattern) !== false) {
                $score -= 2;
                $feedback[] = 'Avoid common words and patterns';
                break;
            }
        }

        // Sequential characters check
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 1;
            $feedback[] = 'Avoid repeated characters';
        }

        // Determine strength level
        if ($score >= 5) {
            $level = 'strong';
        } elseif ($score >= 3) {
            $level = 'medium';
        } else {
            $level = 'weak';
        }

        return [
            'score' => max(0, $score),
            'level' => $level,
            'feedback' => array_unique($feedback),
        ];
    }

    /**
     * Generate security recommendations.
     */
    protected function generateRecommendations(array $results): array
    {
        $recommendations = [];

        if ($results['compromised']) {
            $recommendations[] = 'This password has been compromised. Change it immediately.';
            $recommendations[] = 'Use a unique password for each account.';
            $recommendations[] = 'Consider using a password manager.';
        }

        if ($results['strength']['level'] === 'weak') {
            $recommendations[] = 'Password is too weak. Follow the strength feedback above.';
        }

        if ($results['strength']['level'] === 'medium') {
            $recommendations[] = 'Consider strengthening your password further.';
        }

        $recommendations[] = 'Enable two-factor authentication where possible.';
        $recommendations[] = 'Regularly check for data breaches.';

        return $recommendations;
    }

    /**
     * Generate a secure passkey.
     */
    public function generatePasskey(int $length = 32): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $passkey = '';
        
        for ($i = 0; $i < $length; $i++) {
            $passkey .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $passkey;
    }
} 