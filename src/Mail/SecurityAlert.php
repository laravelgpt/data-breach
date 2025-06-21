<?php

namespace LaravelGPT\DataBreach\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecurityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public string $type;
    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->getSubject();
        
        return $this->subject($subject)
                    ->markdown('data-breach::emails.security-alert')
                    ->with([
                        'type' => $this->type,
                        'data' => $this->data,
                        'title' => $this->getTitle(),
                        'description' => $this->getDescription(),
                        'severity' => $this->getSeverity(),
                        'recommendations' => $this->getRecommendations(),
                    ]);
    }

    /**
     * Get email subject.
     */
    protected function getSubject(): string
    {
        return match ($this->type) {
            'password_breach' => '🚨 Security Alert: Password Compromised',
            'suspicious_ip' => '⚠️ Security Alert: Suspicious IP Detected',
            'malware_detected' => '🦠 Security Alert: Malware Detected',
            'dark_web_breach' => '🌑 Security Alert: Dark Web Breach Found',
            default => '🔐 Security Alert',
        };
    }

    /**
     * Get alert title.
     */
    protected function getTitle(): string
    {
        return match ($this->type) {
            'password_breach' => 'Password Breach Detected',
            'suspicious_ip' => 'Suspicious IP Detected',
            'malware_detected' => 'Malware Detected',
            'dark_web_breach' => 'Dark Web Breach Found',
            default => 'Security Alert',
        };
    }

    /**
     * Get alert description.
     */
    protected function getDescription(): string
    {
        return match ($this->type) {
            'password_breach' => 'A password has been found in one or more data breaches. Immediate action is required.',
            'suspicious_ip' => 'A suspicious IP address has been detected with potential security risks.',
            'malware_detected' => 'Malicious software has been detected in a file or URL scan.',
            'dark_web_breach' => 'Credentials or data have been found on dark web platforms.',
            default => 'A security event has been detected that requires your attention.',
        };
    }

    /**
     * Get alert severity.
     */
    protected function getSeverity(): string
    {
        return match ($this->type) {
            'password_breach' => 'high',
            'malware_detected' => 'high',
            'suspicious_ip' => 'medium',
            'dark_web_breach' => 'medium',
            default => 'low',
        };
    }

    /**
     * Get security recommendations.
     */
    protected function getRecommendations(): array
    {
        return match ($this->type) {
            'password_breach' => [
                'Change the compromised password immediately',
                'Use a unique password for each account',
                'Enable two-factor authentication',
                'Consider using a password manager',
                'Monitor your accounts for suspicious activity',
            ],
            'suspicious_ip' => [
                'Review the IP address and associated activities',
                'Check if the IP is from an expected location',
                'Consider blocking the IP if necessary',
                'Monitor for additional suspicious activity',
                'Update security policies if needed',
            ],
            'malware_detected' => [
                'Do not open or execute the malicious file',
                'Scan your system with antivirus software',
                'Update your security software',
                'Check for other potentially infected files',
                'Consider restoring from a clean backup',
            ],
            'dark_web_breach' => [
                'Change passwords for affected accounts',
                'Enable two-factor authentication',
                'Monitor financial accounts for fraud',
                'Consider credit monitoring services',
                'Report the breach to relevant authorities',
            ],
            default => [
                'Review the security event details',
                'Take appropriate action based on the alert type',
                'Update security measures if necessary',
                'Monitor for similar events in the future',
            ],
        };
    }
} 