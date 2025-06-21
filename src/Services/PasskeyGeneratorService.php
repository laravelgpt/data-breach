<?php

namespace LaravelGPT\DataBreach\Services;

use Illuminate\Support\Facades\Config;

class PasskeyGeneratorService
{
    /**
     * Generate a secure passkey.
     */
    public function generatePasskey(int $length = 32, array $options = []): array
    {
        $defaultOptions = [
            'uppercase' => true,
            'lowercase' => true,
            'numbers' => true,
            'symbols' => true,
            'exclude_similar' => true,
            'exclude_ambiguous' => false,
        ];

        $options = array_merge($defaultOptions, $options);

        $chars = '';
        
        if ($options['lowercase']) {
            $chars .= 'abcdefghijklmnopqrstuvwxyz';
        }
        
        if ($options['uppercase']) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        
        if ($options['numbers']) {
            $chars .= '0123456789';
        }
        
        if ($options['symbols']) {
            $chars .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }

        // Remove similar characters if requested
        if ($options['exclude_similar']) {
            $chars = str_replace(['i', 'l', '1', 'I', 'O', '0'], '', $chars);
        }

        // Remove ambiguous characters if requested
        if ($options['exclude_ambiguous']) {
            $chars = str_replace(['{', '}', '[', ']', '(', ')', '/', '\\', '"', "'", '`', '~', ',', ';', ':', '.', '<', '>'], '', $chars);
        }

        if (empty($chars)) {
            throw new \InvalidArgumentException('No character sets selected for passkey generation');
        }

        $passkey = '';
        $charLength = strlen($chars);
        
        for ($i = 0; $i < $length; $i++) {
            $passkey .= $chars[random_int(0, $charLength - 1)];
        }

        return [
            'passkey' => $passkey,
            'length' => $length,
            'strength' => $this->analyzePasskeyStrength($passkey),
            'options' => $options,
        ];
    }

    /**
     * Generate a memorable passphrase.
     */
    public function generatePassphrase(int $wordCount = 4, string $separator = '-'): array
    {
        $words = $this->getWordList();
        $passphrase = '';
        
        for ($i = 0; $i < $wordCount; $i++) {
            $word = $words[array_rand($words)];
            $passphrase .= ($i > 0 ? $separator : '') . $word;
        }

        return [
            'passphrase' => $passphrase,
            'word_count' => $wordCount,
            'separator' => $separator,
            'strength' => $this->analyzePasskeyStrength($passphrase),
        ];
    }

    /**
     * Generate a PIN code.
     */
    public function generatePin(int $length = 6): array
    {
        $pin = '';
        for ($i = 0; $i < $length; $i++) {
            $pin .= random_int(0, 9);
        }

        return [
            'pin' => $pin,
            'length' => $length,
            'strength' => $this->analyzePasskeyStrength($pin),
        ];
    }

    /**
     * Analyze passkey strength.
     */
    public function analyzePasskeyStrength(string $passkey): array
    {
        $score = 0;
        $feedback = [];

        // Length check
        $length = strlen($passkey);
        if ($length >= 16) {
            $score += 3;
        } elseif ($length >= 12) {
            $score += 2;
        } elseif ($length >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Passkey should be at least 8 characters long';
        }

        // Character variety checks
        if (preg_match('/[A-Z]/', $passkey)) {
            $score += 1;
        } else {
            $feedback[] = 'Add uppercase letters';
        }

        if (preg_match('/[a-z]/', $passkey)) {
            $score += 1;
        } else {
            $feedback[] = 'Add lowercase letters';
        }

        if (preg_match('/[0-9]/', $passkey)) {
            $score += 1;
        } else {
            $feedback[] = 'Add numbers';
        }

        if (preg_match('/[^A-Za-z0-9]/', $passkey)) {
            $score += 1;
        } else {
            $feedback[] = 'Add special characters';
        }

        // Entropy calculation
        $entropy = $this->calculateEntropy($passkey);
        $score += min(3, floor($entropy / 10));

        // Determine strength level
        if ($score >= 8) {
            $level = 'very_strong';
        } elseif ($score >= 6) {
            $level = 'strong';
        } elseif ($score >= 4) {
            $level = 'medium';
        } else {
            $level = 'weak';
        }

        return [
            'score' => $score,
            'level' => $level,
            'entropy' => $entropy,
            'feedback' => array_unique($feedback),
        ];
    }

    /**
     * Calculate entropy of a passkey.
     */
    protected function calculateEntropy(string $passkey): float
    {
        $charSet = [];
        $length = strlen($passkey);

        for ($i = 0; $i < $length; $i++) {
            $char = $passkey[$i];
            if (ctype_lower($char)) {
                $charSet['lowercase'] = 26;
            } elseif (ctype_upper($char)) {
                $charSet['uppercase'] = 26;
            } elseif (ctype_digit($char)) {
                $charSet['numbers'] = 10;
            } else {
                $charSet['symbols'] = 32;
            }
        }

        $possibleChars = array_sum($charSet);
        return $length * log($possibleChars, 2);
    }

    /**
     * Get word list for passphrases.
     */
    protected function getWordList(): array
    {
        return [
            'apple', 'banana', 'cherry', 'dragon', 'eagle', 'forest', 'garden', 'house',
            'island', 'jungle', 'knight', 'lemon', 'mountain', 'ocean', 'planet', 'queen',
            'river', 'sunset', 'tiger', 'umbrella', 'village', 'window', 'yellow', 'zebra',
            'anchor', 'bridge', 'castle', 'diamond', 'elephant', 'feather', 'guitar', 'hammer',
            'iceberg', 'jacket', 'kangaroo', 'lighthouse', 'moonlight', 'notebook', 'orange', 'penguin',
            'rainbow', 'sailboat', 'treasure', 'umbrella', 'volcano', 'waterfall', 'xylophone', 'yacht',
        ];
    }

    /**
     * Get 2FA recommendations.
     */
    public function get2FARecommendations(): array
    {
        return [
            'authenticator_apps' => [
                'name' => 'Authenticator Apps',
                'description' => 'Time-based one-time password (TOTP) apps',
                'apps' => [
                    'Google Authenticator',
                    'Microsoft Authenticator',
                    'Authy',
                    '1Password',
                    'Bitwarden',
                ],
                'setup_steps' => [
                    'Download an authenticator app',
                    'Scan the QR code provided by the service',
                    'Enter the 6-digit code to verify setup',
                    'Store backup codes in a secure location',
                ],
                'security_level' => 'high',
            ],
            'hardware_keys' => [
                'name' => 'Hardware Security Keys',
                'description' => 'Physical security keys (FIDO2/U2F)',
                'keys' => [
                    'YubiKey',
                    'Google Titan',
                    'Feitian',
                    'SoloKey',
                ],
                'setup_steps' => [
                    'Purchase a compatible hardware key',
                    'Register the key with your account',
                    'Test the key to ensure it works',
                    'Keep a backup key in a secure location',
                ],
                'security_level' => 'very_high',
            ],
            'sms_2fa' => [
                'name' => 'SMS 2FA',
                'description' => 'Text message verification',
                'setup_steps' => [
                    'Enter your phone number',
                    'Receive a verification code via SMS',
                    'Enter the code to verify setup',
                ],
                'security_level' => 'medium',
                'warning' => 'SMS 2FA is vulnerable to SIM swapping attacks',
            ],
            'email_2fa' => [
                'name' => 'Email 2FA',
                'description' => 'Email verification codes',
                'setup_steps' => [
                    'Enter your email address',
                    'Receive a verification code via email',
                    'Enter the code to verify setup',
                ],
                'security_level' => 'low',
                'warning' => 'Email 2FA is less secure than authenticator apps',
            ],
        ];
    }

    /**
     * Generate backup codes.
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = '';
            for ($j = 0; $j < 8; $j++) {
                $code .= strtoupper(substr(md5(random_bytes(1)), 0, 1));
            }
            $codes[] = $code;
        }

        return [
            'codes' => $codes,
            'count' => $count,
            'generated_at' => now()->toISOString(),
        ];
    }
} 