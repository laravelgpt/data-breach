<?php

namespace LaravelGPT\DataBreach\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class IpReputationService
{
    /**
     * Check IP reputation across multiple services.
     */
    public function checkIp(string $ip): array
    {
        $cacheKey = "ip_reputation_{$ip}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $results = [
            'ip' => $ip,
            'suspicious' => false,
            'risk_score' => 0,
            'threats' => [],
            'location' => null,
            'reputation' => 'clean',
            'sources' => [],
        ];

        // Check AbuseIPDB
        $abuseipdbResult = $this->checkAbuseIPDB($ip);
        if ($abuseipdbResult['suspicious']) {
            $results['suspicious'] = true;
            $results['risk_score'] += $abuseipdbResult['score'];
            $results['threats'] = array_merge($results['threats'], $abuseipdbResult['threats']);
            $results['sources'][] = 'AbuseIPDB';
        }

        // Check IPQS
        $ipqsResult = $this->checkIPQS($ip);
        if ($ipqsResult['suspicious']) {
            $results['suspicious'] = true;
            $results['risk_score'] += $ipqsResult['score'];
            $results['threats'] = array_merge($results['threats'], $ipqsResult['threats']);
            $results['sources'][] = 'IPQS';
        }

        // Check VirusTotal
        $virustotalResult = $this->checkVirusTotal($ip);
        if ($virustotalResult['suspicious']) {
            $results['suspicious'] = true;
            $results['risk_score'] += $virustotalResult['score'];
            $results['threats'] = array_merge($results['threats'], $virustotalResult['threats']);
            $results['sources'][] = 'VirusTotal';
        }

        // Get location data
        $results['location'] = $this->getLocationData($ip);

        // Determine reputation level
        $results['reputation'] = $this->determineReputation($results['risk_score']);

        // Check geo-restrictions
        if (Config::get('data-breach.ip_reputation.geo_restrictions.enabled', false)) {
            $geoResult = $this->checkGeoRestrictions($results['location']);
            if ($geoResult['blocked']) {
                $results['suspicious'] = true;
                $results['threats'][] = 'Geographic restriction';
            }
        }

        // Cache the result
        $ttl = Config::get('data-breach.cache.ttl.ip_check', 1800);
        Cache::put($cacheKey, $results, $ttl);

        // Log if suspicious
        if ($results['suspicious'] && Config::get('data-breach.logging.log_suspicious_ips', true)) {
            Log::warning('Suspicious IP detected', [
                'ip' => $ip,
                'risk_score' => $results['risk_score'],
                'threats' => $results['threats'],
                'sources' => $results['sources'],
            ]);
        }

        return $results;
    }

    /**
     * Check IP against AbuseIPDB.
     */
    protected function checkAbuseIPDB(string $ip): array
    {
        $apiKey = Config::get('data-breach.apis.abuseipdb');
        
        if (!$apiKey) {
            return ['suspicious' => false, 'score' => 0, 'threats' => []];
        }

        try {
            $response = Http::withHeaders([
                'Key' => $apiKey,
                'Accept' => 'application/json',
            ])->get('https://api.abuseipdb.com/api/v2/check', [
                'ipAddress' => $ip,
                'maxAgeInDays' => 90,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'];
                $abuseConfidenceScore = $data['abuseConfidenceScore'] ?? 0;
                $isPublic = $data['isPublic'] ?? false;
                $isWhitelisted = $data['isWhitelisted'] ?? false;
                
                $threats = [];
                if ($abuseConfidenceScore > 25) {
                    $threats[] = 'High abuse confidence';
                }
                if (!$isPublic) {
                    $threats[] = 'Private IP range';
                }

                return [
                    'suspicious' => $abuseConfidenceScore > 25 || !$isPublic,
                    'score' => $abuseConfidenceScore,
                    'threats' => $threats,
                ];
            }
        } catch (\Exception $e) {
            Log::error('AbuseIPDB API error', ['error' => $e->getMessage()]);
        }

        return ['suspicious' => false, 'score' => 0, 'threats' => []];
    }

    /**
     * Check IP against IPQS.
     */
    protected function checkIPQS(string $ip): array
    {
        $apiKey = Config::get('data-breach.apis.ipqs');
        
        if (!$apiKey) {
            return ['suspicious' => false, 'score' => 0, 'threats' => []];
        }

        try {
            $response = Http::get('https://www.ipqualityscore.com/api/json/ip/' . $apiKey . '/' . $ip);

            if ($response->successful()) {
                $data = $response->json();
                $fraudScore = $data['fraud_score'] ?? 0;
                $proxy = $data['proxy'] ?? false;
                $vpn = $data['vpn'] ?? false;
                $tor = $data['tor'] ?? false;
                $bot = $data['bot_status'] ?? false;
                
                $threats = [];
                if ($proxy) $threats[] = 'Proxy detected';
                if ($vpn) $threats[] = 'VPN detected';
                if ($tor) $threats[] = 'Tor exit node';
                if ($bot) $threats[] = 'Bot activity';

                return [
                    'suspicious' => $fraudScore > 75 || $proxy || $vpn || $tor || $bot,
                    'score' => $fraudScore,
                    'threats' => $threats,
                ];
            }
        } catch (\Exception $e) {
            Log::error('IPQS API error', ['error' => $e->getMessage()]);
        }

        return ['suspicious' => false, 'score' => 0, 'threats' => []];
    }

    /**
     * Check IP against VirusTotal.
     */
    protected function checkVirusTotal(string $ip): array
    {
        $apiKey = Config::get('data-breach.apis.virustotal');
        
        if (!$apiKey) {
            return ['suspicious' => false, 'score' => 0, 'threats' => []];
        }

        try {
            $response = Http::withHeaders([
                'x-apikey' => $apiKey,
            ])->get("https://www.virustotal.com/api/v3/ip_addresses/{$ip}");

            if ($response->successful()) {
                $data = $response->json()['data'];
                $attributes = $data['attributes'];
                $lastAnalysisStats = $attributes['last_analysis_stats'] ?? [];
                $malicious = $lastAnalysisStats['malicious'] ?? 0;
                $suspicious = $lastAnalysisStats['suspicious'] ?? 0;
                
                $threats = [];
                if ($malicious > 0) $threats[] = "Malicious activity ({$malicious} detections)";
                if ($suspicious > 0) $threats[] = "Suspicious activity ({$suspicious} detections)";

                $score = ($malicious * 10) + ($suspicious * 5);

                return [
                    'suspicious' => $malicious > 0 || $suspicious > 0,
                    'score' => $score,
                    'threats' => $threats,
                ];
            }
        } catch (\Exception $e) {
            Log::error('VirusTotal API error', ['error' => $e->getMessage()]);
        }

        return ['suspicious' => false, 'score' => 0, 'threats' => []];
    }

    /**
     * Get location data for IP.
     */
    protected function getLocationData(string $ip): ?array
    {
        try {
            $response = Http::get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'city' => $data['city'] ?? null,
                        'isp' => $data['isp'] ?? null,
                        'org' => $data['org'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('IP geolocation error', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Check geographic restrictions.
     */
    protected function checkGeoRestrictions(?array $location): array
    {
        if (!$location) {
            return ['blocked' => false];
        }

        $allowedCountries = Config::get('data-breach.ip_reputation.geo_restrictions.allowed_countries', []);
        $blockedCountries = Config::get('data-breach.ip_reputation.geo_restrictions.blocked_countries', []);

        $countryCode = $location['country_code'] ?? null;

        if ($countryCode) {
            if (!empty($allowedCountries) && !in_array($countryCode, $allowedCountries)) {
                return ['blocked' => true, 'reason' => 'Country not in allowed list'];
            }

            if (in_array($countryCode, $blockedCountries)) {
                return ['blocked' => true, 'reason' => 'Country in blocked list'];
            }
        }

        return ['blocked' => false];
    }

    /**
     * Determine reputation level based on risk score.
     */
    protected function determineReputation(int $riskScore): string
    {
        if ($riskScore >= 80) {
            return 'high_risk';
        } elseif ($riskScore >= 50) {
            return 'medium_risk';
        } elseif ($riskScore >= 20) {
            return 'low_risk';
        } else {
            return 'clean';
        }
    }
} 