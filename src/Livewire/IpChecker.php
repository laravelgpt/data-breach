<?php

namespace LaravelGPT\DataBreach\Livewire;

use Livewire\Component;
use LaravelGPT\DataBreach\Services\IpReputationService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class IpChecker extends Component
{
    public string $ipAddress = '';
    public ?array $result = null;
    public bool $isLoading = false;
    public string $error = '';

    public function checkIp()
    {
        $this->reset(['result', 'error']);
        
        if (empty($this->ipAddress)) {
            $this->error = 'Please enter an IP address';
            return;
        }

        if (!filter_var($this->ipAddress, FILTER_VALIDATE_IP)) {
            $this->error = 'Please enter a valid IP address';
            return;
        }

        // Rate limiting
        $key = 'ip-check-' . Request::ip();
        if (RateLimiter::tooManyAttempts($key, Config::get('data-breach.rate_limiting.ip_check', 120))) {
            $this->error = 'Too many requests. Please try again later.';
            return;
        }

        RateLimiter::hit($key);

        $this->isLoading = true;

        try {
            $ipService = App::make(IpReputationService::class);
            $this->result = $ipService->checkIp($this->ipAddress);
        } catch (\Exception $e) {
            $this->error = 'Error checking IP address: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return View::make('data-breach::livewire.ip-checker');
    }
} 