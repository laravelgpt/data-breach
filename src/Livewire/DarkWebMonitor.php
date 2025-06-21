<?php

namespace LaravelGPT\DataBreach\Livewire;

use Livewire\Component;
use LaravelGPT\DataBreach\Services\DarkWebMonitorService;
use Illuminate\Support\Facades\RateLimiter;

class DarkWebMonitor extends Component
{
    public string $email = '';
    public string $domain = '';
    public ?array $result = null;
    public bool $isLoading = false;
    public string $error = '';
    public string $searchType = 'email'; // 'email' or 'domain'

    public function searchEmail()
    {
        $this->reset(['result', 'error']);
        
        if (empty($this->email)) {
            $this->error = 'Please enter an email address';
            return;
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->error = 'Please enter a valid email address';
            return;
        }

        // Rate limiting
        $key = 'dark-web-search-' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, config('data-breach.rate_limiting.dark_web_search', 30))) {
            $this->error = 'Too many requests. Please try again later.';
            return;
        }

        RateLimiter::hit($key);

        $this->isLoading = true;

        try {
            $darkWebService = app(DarkWebMonitorService::class);
            $this->result = $darkWebService->searchEmail($this->email);
        } catch (\Exception $e) {
            $this->error = 'Error searching dark web: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function searchDomain()
    {
        $this->reset(['result', 'error']);
        
        if (empty($this->domain)) {
            $this->error = 'Please enter a domain name';
            return;
        }

        // Rate limiting
        $key = 'dark-web-search-' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, config('data-breach.rate_limiting.dark_web_search', 30))) {
            $this->error = 'Too many requests. Please try again later.';
            return;
        }

        RateLimiter::hit($key);

        $this->isLoading = true;

        try {
            $darkWebService = app(DarkWebMonitorService::class);
            $this->result = $darkWebService->searchDomain($this->domain);
        } catch (\Exception $e) {
            $this->error = 'Error searching dark web: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('data-breach::livewire.dark-web-monitor');
    }
} 