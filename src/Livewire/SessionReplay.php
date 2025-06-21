<?php

namespace LaravelGPT\DataBreach\Livewire;

use Livewire\Component;
use LaravelGPT\DataBreach\Services\SessionReplayService;

class SessionReplay extends Component
{
    public string $sessionKey;
    public array $sessionData = [];
    public array $analytics = [];
    public bool $isRecording = false;
    public string $status = 'idle';

    public function mount()
    {
        $this->sessionKey = session()->getId();
    }

    public function render()
    {
        return view('data-breach::livewire.session-replay');
    }

    public function startRecording()
    {
        $replayService = app(SessionReplayService::class);
        $this->sessionKey = $replayService->startRecording(request());
        $this->isRecording = true;
        $this->status = 'recording';
        
        $this->dispatch('session-recording-started', sessionKey: $this->sessionKey);
    }

    public function stopRecording()
    {
        $replayService = app(SessionReplayService::class);
        $this->sessionData = $replayService->stopRecording($this->sessionKey);
        $this->isRecording = false;
        $this->status = 'completed';
        
        $this->dispatch('session-recording-stopped');
    }

    public function recordEvent($type, $data, $screenshot = null)
    {
        if (!$this->isRecording) {
            return;
        }

        $replayService = app(SessionReplayService::class);
        $replayService->recordEvent($this->sessionKey, [
            'type' => $type,
            'data' => $data,
            'screenshot' => $screenshot,
        ]);
    }

    public function getReplay()
    {
        $replayService = app(SessionReplayService::class);
        $this->sessionData = $replayService->getSessionReplay($this->sessionKey) ?? [];
    }

    public function getAnalytics()
    {
        $replayService = app(SessionReplayService::class);
        $this->analytics = $replayService->getSessionAnalytics($this->sessionKey);
    }

    public function cleanup()
    {
        $replayService = app(SessionReplayService::class);
        $replayService->cleanupOldSessions();
        
        $this->dispatch('sessions-cleaned-up');
    }
} 