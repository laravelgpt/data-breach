<?php

namespace LaravelGPT\DataBreach\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelGPT\DataBreach\Services\PasskeyGeneratorService;

class PasskeyController
{
    public function __construct(
        private PasskeyGeneratorService $generatorService
    ) {}

    /**
     * Generate a secure passkey.
     */
    public function generatePasskey(Request $request): JsonResponse
    {
        $request->validate([
            'length' => 'integer|min:8|max:128',
            'uppercase' => 'boolean',
            'lowercase' => 'boolean',
            'numbers' => 'boolean',
            'symbols' => 'boolean',
            'exclude_similar' => 'boolean',
            'exclude_ambiguous' => 'boolean',
        ]);

        try {
            $length = $request->input('length', 32);
            $options = $request->only([
                'uppercase', 'lowercase', 'numbers', 'symbols',
                'exclude_similar', 'exclude_ambiguous'
            ]);

            $result = $this->generatorService->generatePasskey($length, $options);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating passkey: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a memorable passphrase.
     */
    public function generatePassphrase(Request $request): JsonResponse
    {
        $request->validate([
            'word_count' => 'integer|min:3|max:10',
            'separator' => 'string|max:5',
        ]);

        try {
            $wordCount = $request->input('word_count', 4);
            $separator = $request->input('separator', '-');

            $result = $this->generatorService->generatePassphrase($wordCount, $separator);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating passphrase: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a PIN code.
     */
    public function generatePin(Request $request): JsonResponse
    {
        $request->validate([
            'length' => 'integer|min:4|max:12',
        ]);

        try {
            $length = $request->input('length', 6);
            $result = $this->generatorService->generatePin($length);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating PIN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate backup codes.
     */
    public function generateBackupCodes(Request $request): JsonResponse
    {
        $request->validate([
            'count' => 'integer|min:5|max:20',
        ]);

        try {
            $count = $request->input('count', 10);
            $result = $this->generatorService->generateBackupCodes($count);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating backup codes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get 2FA recommendations.
     */
    public function get2FARecommendations(): JsonResponse
    {
        try {
            $recommendations = $this->generatorService->get2FARecommendations();

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting 2FA recommendations: ' . $e->getMessage(),
            ], 500);
        }
    }
} 