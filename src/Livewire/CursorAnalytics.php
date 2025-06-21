<?php

namespace LaravelGPT\DataBreach\Livewire;

use Livewire\Component;
use LaravelGPT\DataBreach\Services\CursorAnalyticsService;

class CursorAnalytics extends Component
{
    public string $sessionKey;
    public array $analytics = [];
    public array $events = [];
    public bool $isTracking = false;

    public function mount()
    {
        $this->sessionKey = session()->getId();
    }

    public function render()
    {
        return view('data-breach::livewire.cursor-analytics');
    }

    public function startTracking()
    {
        $this->isTracking = true;
        $this->dispatch('cursor-tracking-started');
    }

    public function stopTracking()
    {
        $this->isTracking = false;
        $this->dispatch('cursor-tracking-stopped');
    }

    public function getAnalytics()
    {
        $analyticsService = app(CursorAnalyticsService::class);
        $this->analytics = $analyticsService->getSessionAnalytics($this->sessionKey);
    }

    public function getEvents()
    {
        $analyticsService = app(CursorAnalyticsService::class);
        $this->events = $analyticsService->getSessionEvents($this->sessionKey);
    }

    public function archiveData()
    {
        $analyticsService = app(CursorAnalyticsService::class);
        $analyticsService->archiveOldData();
        
        $this->dispatch('data-archived');
    }

    public function trackEvent($x, $y, $eventType, $elementId = null, $elementType = null, $velocity = 0)
    {
        if (!$this->isTracking) {
            return;
        }

        $analyticsService = app(CursorAnalyticsService::class);
        $analyticsService->trackCursorEvent(request(), [
            'x' => $x,
            'y' => $y,
            'event_type' => $eventType,
            'element_id' => $elementId,
            'element_type' => $elementType,
            'velocity' => $velocity,
        ]);
    }
} 