<?php

namespace LaravelGPT\DataBreach\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelGPT\DataBreach\Services\DarkWebMonitorService;
use LaravelGPT\DataBreach\Services\AlertDispatcherService;

class DarkWebController
{
    public function __construct(
        private DarkWebMonitorService $darkWebService,
        private AlertDispatcherService $alertService
    ) {}

    /**
     * Search dark web for query.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'type' => 'string|in:email,domain',
        ]);

        try {
            $query = $request->input('query');
            $type = $request->input('type', 'email');
            
            $result = $this->darkWebService->searchDarkWeb($query, $type);

            // Send alert if found
            if ($result['found']) {
                $this->alertService->sendAlert('dark_web_breach', [
                    'query' => $query,
                    'type' => $type,
                    'total_breaches' => $result['total_breaches'],
                    'sources' => $result['sources'],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching dark web: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Monitor email for breaches.
     */
    public function monitorEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        try {
            $email = $request->input('email');
            $result = $this->darkWebService->monitorEmail($email);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error monitoring email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Monitor domain for breaches.
     */
    public function monitorDomain(Request $request): JsonResponse
    {
        $request->validate([
            'domain' => 'required|string',
        ]);

        try {
            $domain = $request->input('domain');
            $result = $this->darkWebService->monitorDomain($domain);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error monitoring domain: ' . $e->getMessage(),
            ], 500);
        }
    }
} 