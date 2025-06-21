<?php

namespace LaravelGPT\DataBreach\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use LaravelGPT\DataBreach\Services\PasswordBreachService;
use LaravelGPT\DataBreach\Services\PasskeyGeneratorService;
use LaravelGPT\DataBreach\Services\AlertDispatcherService;

class PasswordChecker extends Component
{
    #[Rule('required|min:1')]
    public string $password = '';

    public array $breachResult = [];
    public array $strengthResult = [];
    public array $generatedPasskey = [];
    public array $recommendations = [];
    public bool $isLoading = false;
    public bool $showPasskey = false;
    public bool $showRecommendations = false;

    public function mount()
    {
        $this->breachResult = [
            'compromised' => false,
            'breach_count' => 0,
            'sources' => [],
            'strength' => [],
            'recommendations' => [],
        ];

        $this->strengthResult = [
            'score' => 0,
            'level' => 'weak',
            'feedback' => [],
        ];
    }

    public function checkPassword()
    {
        $this->validate();

        if (empty($this->password)) {
            return;
        }

        $this->isLoading = true;

        try {
            $breachService = app(PasswordBreachService::class);
            $this->breachResult = $breachService->checkPassword($this->password);
            $this->strengthResult = $this->breachResult['strength'];
            $this->recommendations = $this->breachResult['recommendations'];

            // Send alert if compromised
            if ($this->breachResult['compromised']) {
                $alertService = app(AlertDispatcherService::class);
                $alertService->sendAlert('password_breach', [
                    'password' => $this->password,
                    'breach_count' => $this->breachResult['breach_count'],
                    'sources' => $this->breachResult['sources'],
                    'strength' => $this->strengthResult,
                ]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error checking password: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    public function generatePasskey()
    {
        try {
            $generatorService = app(PasskeyGeneratorService::class);
            $this->generatedPasskey = $generatorService->generatePasskey(32);
            $this->showPasskey = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating passkey: ' . $e->getMessage());
        }
    }

    public function generatePassphrase()
    {
        try {
            $generatorService = app(PasskeyGeneratorService::class);
            $this->generatedPasskey = $generatorService->generatePassphrase(4);
            $this->showPasskey = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating passphrase: ' . $e->getMessage());
        }
    }

    public function copyToClipboard($text)
    {
        $this->dispatch('copy-to-clipboard', text: $text);
        session()->flash('success', 'Copied to clipboard!');
    }

    public function getStrengthColor()
    {
        return match ($this->strengthResult['level']) {
            'very_strong' => 'text-green-600',
            'strong' => 'text-green-500',
            'medium' => 'text-yellow-500',
            'weak' => 'text-red-500',
            default => 'text-gray-500',
        };
    }

    public function getStrengthBgColor()
    {
        return match ($this->strengthResult['level']) {
            'very_strong' => 'bg-green-600',
            'strong' => 'bg-green-500',
            'medium' => 'bg-yellow-500',
            'weak' => 'bg-red-500',
            default => 'bg-gray-500',
        };
    }

    public function getBreachStatusColor()
    {
        return $this->breachResult['compromised'] ? 'text-red-600' : 'text-green-600';
    }

    public function getBreachStatusIcon()
    {
        return $this->breachResult['compromised'] ? '🔓' : '🔒';
    }

    public function render()
    {
        return view('data-breach::livewire.password-checker');
    }
} 