<?php

namespace LaravelGPT\DataBreach\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelGPT\DataBreach\Services\IpReputationService;
use LaravelGPT\DataBreach\Services\AlertDispatcherService;

class IpController
{
    public function __construct(
        private IpReputationService $ipService,
        private AlertDispatcherService $alertService
    ) {}

    /**
     * Check IP reputation.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'ip' => 'required|string|ip',
        ]);

        try {
            $ip = $request->input('ip');
            $result = $this->ipService->checkIp($ip);

            // Send alert if suspicious
            if ($result['suspicious']) {
                $this->alertService->sendAlert('suspicious_ip', [
                    'ip' => $ip,
                    'risk_score' => $result['risk_score'],
                    'threats' => $result['threats'],
                    'location' => $result['location'],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking IP: ' . $e->getMessage(),
            ], 500);
        }
    }
} 