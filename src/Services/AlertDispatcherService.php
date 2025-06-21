<?php

namespace LaravelGPT\DataBreach\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use LaravelGPT\DataBreach\Mail\SecurityAlert;

class AlertDispatcherService
{
    /**
     * Send security alert.
     */
    public function sendAlert(string $type, array $data, array $recipients = []): bool
    {
        $success = true;

        // Send email alerts
        if (Config::get('data-breach.alerts.email.enabled', true)) {
            $emailSuccess = $this->sendEmailAlert($type, $data, $recipients);
            $success = $success && $emailSuccess;
        }

        // Send Telegram alerts
        if (Config::get('data-breach.alerts.telegram.enabled', false)) {
            $telegramSuccess = $this->sendTelegramAlert($type, $data);
            $success = $success && $telegramSuccess;
        }

        // Send Slack alerts
        if (Config::get('data-breach.alerts.slack.enabled', false)) {
            $slackSuccess = $this->sendSlackAlert($type, $data);
            $success = $success && $slackSuccess;
        }

        return $success;
    }

    /**
     * Send email alert.
     */
    protected function sendEmailAlert(string $type, array $data, array $recipients = []): bool
    {
        try {
            $defaultRecipients = Config::get('data-breach.alerts.email.recipients', []);
            $allRecipients = array_merge($defaultRecipients, $recipients);

            if (empty($allRecipients)) {
                Log::warning('No email recipients configured for security alerts');
                return false;
            }

            foreach ($allRecipients as $recipient) {
                Mail::to($recipient)->send(new SecurityAlert($type, $data));
            }

            Log::info('Security alert email sent', [
                'type' => $type,
                'recipients' => $allRecipients,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send security alert email', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send Telegram alert.
     */
    protected function sendTelegramAlert(string $type, array $data): bool
    {
        try {
            $botToken = Config::get('data-breach.alerts.telegram.bot_token');
            $chatId = Config::get('data-breach.alerts.telegram.chat_id');

            if (!$botToken || !$chatId) {
                Log::warning('Telegram bot token or chat ID not configured');
                return false;
            }

            $message = $this->formatTelegramMessage($type, $data);

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('Security alert Telegram message sent', [
                    'type' => $type,
                    'chat_id' => $chatId,
                ]);

                return true;
            } else {
                Log::error('Failed to send Telegram alert', [
                    'type' => $type,
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram alert', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send Slack alert.
     */
    protected function sendSlackAlert(string $type, array $data): bool
    {
        try {
            $webhookUrl = Config::get('data-breach.alerts.slack.webhook_url');

            if (!$webhookUrl) {
                Log::warning('Slack webhook URL not configured');
                return false;
            }

            $payload = $this->formatSlackMessage($type, $data);

            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Security alert Slack message sent', [
                    'type' => $type,
                ]);

                return true;
            } else {
                Log::error('Failed to send Slack alert', [
                    'type' => $type,
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Slack alert', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format Telegram message.
     */
    protected function formatTelegramMessage(string $type, array $data): string
    {
        $emoji = $this->getAlertEmoji($type);
        $title = $this->getAlertTitle($type);

        $message = "{$emoji} <b>{$title}</b>\n\n";

        switch ($type) {
            case 'password_breach':
                $message .= "🔍 <b>Password:</b> {$data['password']}\n";
                $message .= "📊 <b>Breach Count:</b> {$data['breach_count']}\n";
                $message .= "🔗 <b>Sources:</b> " . implode(', ', $data['sources']) . "\n";
                $message .= "💪 <b>Strength:</b> {$data['strength']['level']}\n";
                break;

            case 'suspicious_ip':
                $message .= "🌐 <b>IP Address:</b> {$data['ip']}\n";
                $message .= "⚠️ <b>Risk Score:</b> {$data['risk_score']}\n";
                $message .= "🚨 <b>Threats:</b> " . implode(', ', $data['threats']) . "\n";
                if ($data['location']) {
                    $message .= "📍 <b>Location:</b> {$data['location']['city']}, {$data['location']['country']}\n";
                }
                break;

            case 'malware_detected':
                $message .= "🦠 <b>File:</b> {$data['file_path']}\n";
                $message .= "📊 <b>Detection Ratio:</b> {$data['detection_ratio']}%\n";
                $message .= "🔍 <b>Total Scanners:</b> {$data['total_scanners']}\n";
                break;

            case 'dark_web_breach':
                $message .= "🌑 <b>Query:</b> {$data['query']}\n";
                $message .= "📊 <b>Total Breaches:</b> {$data['total_breaches']}\n";
                $message .= "🔗 <b>Sources:</b> " . implode(', ', $data['sources']) . "\n";
                break;
        }

        $message .= "\n⏰ <b>Time:</b> " . now()->format('Y-m-d H:i:s T');
        $message .= "\n🔐 <b>Source:</b> LaravelGPT Data Breach Monitor";

        return $message;
    }

    /**
     * Format Slack message.
     */
    protected function formatSlackMessage(string $type, array $data): array
    {
        $emoji = $this->getAlertEmoji($type);
        $title = $this->getAlertTitle($type);
        $color = $this->getAlertColor($type);

        $fields = [];

        switch ($type) {
            case 'password_breach':
                $fields = [
                    ['title' => 'Password', 'value' => $data['password'], 'short' => true],
                    ['title' => 'Breach Count', 'value' => $data['breach_count'], 'short' => true],
                    ['title' => 'Sources', 'value' => implode(', ', $data['sources']), 'short' => false],
                    ['title' => 'Strength', 'value' => $data['strength']['level'], 'short' => true],
                ];
                break;

            case 'suspicious_ip':
                $fields = [
                    ['title' => 'IP Address', 'value' => $data['ip'], 'short' => true],
                    ['title' => 'Risk Score', 'value' => $data['risk_score'], 'short' => true],
                    ['title' => 'Threats', 'value' => implode(', ', $data['threats']), 'short' => false],
                ];
                if ($data['location']) {
                    $fields[] = ['title' => 'Location', 'value' => "{$data['location']['city']}, {$data['location']['country']}", 'short' => true];
                }
                break;

            case 'malware_detected':
                $fields = [
                    ['title' => 'File', 'value' => $data['file_path'], 'short' => false],
                    ['title' => 'Detection Ratio', 'value' => $data['detection_ratio'] . '%', 'short' => true],
                    ['title' => 'Total Scanners', 'value' => $data['total_scanners'], 'short' => true],
                ];
                break;

            case 'dark_web_breach':
                $fields = [
                    ['title' => 'Query', 'value' => $data['query'], 'short' => true],
                    ['title' => 'Total Breaches', 'value' => $data['total_breaches'], 'short' => true],
                    ['title' => 'Sources', 'value' => implode(', ', $data['sources']), 'short' => false],
                ];
                break;
        }

        return [
            'attachments' => [
                [
                    'color' => $color,
                    'title' => "{$emoji} {$title}",
                    'fields' => $fields,
                    'footer' => 'LaravelGPT Data Breach Monitor',
                    'ts' => time(),
                ]
            ]
        ];
    }

    /**
     * Get alert emoji.
     */
    protected function getAlertEmoji(string $type): string
    {
        return match ($type) {
            'password_breach' => '🔓',
            'suspicious_ip' => '🚨',
            'malware_detected' => '🦠',
            'dark_web_breach' => '🌑',
            default => '⚠️',
        };
    }

    /**
     * Get alert title.
     */
    protected function getAlertTitle(string $type): string
    {
        return match ($type) {
            'password_breach' => 'Password Breach Detected',
            'suspicious_ip' => 'Suspicious IP Detected',
            'malware_detected' => 'Malware Detected',
            'dark_web_breach' => 'Dark Web Breach Found',
            default => 'Security Alert',
        };
    }

    /**
     * Get alert color for Slack.
     */
    protected function getAlertColor(string $type): string
    {
        return match ($type) {
            'password_breach' => '#ff0000',
            'suspicious_ip' => '#ffa500',
            'malware_detected' => '#ff0000',
            'dark_web_breach' => '#800080',
            default => '#ffa500',
        };
    }
} 