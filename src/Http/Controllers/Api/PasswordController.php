<?php

namespace LaravelGPT\DataBreach\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelGPT\DataBreach\Services\PasswordBreachService;
use LaravelGPT\DataBreach\Services\AlertDispatcherService;

class PasswordController
{
    public function __construct(
        private PasswordBreachService $breachService,
        private AlertDispatcherService $alertService
    ) {}

    /**
     * Check password for breaches.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:1',
        ]);

        try {
            $password = $request->input('password');
            $result = $this->breachService->checkPassword($password);

            // Send alert if compromised
            if ($result['compromised']) {
                $this->alertService->sendAlert('password_breach', [
                    'password' => $password,
                    'breach_count' => $result['breach_count'],
                    'sources' => $result['sources'],
                    'strength' => $result['strength'],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking password: ' . $e->getMessage(),
            ], 500);
        }
    }
} 