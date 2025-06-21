<?php

namespace LaravelGPT\DataBreach\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'data-breach:install';
    protected $description = 'Install LaravelGPT Data Breach package with interactive frontend selection';

    public function handle()
    {
        $this->info('🚀 LaravelGPT Data Breach Installer');
        $this->info('=====================================');

        // Publish configuration
        $this->call('vendor:publish', ['--tag' => 'data-breach-config']);

        // Choose frontend stack
        $frontend = $this->choice(
            'Choose your preferred frontend stack:',
            [
                'livewire' => 'Livewire 3 (Default) - Real-time reactive components',
                'volt' => 'Volt - Laravel 12 component system',
                'vue' => 'Vue.js - Modern reactive framework',
                'react' => 'React - Component-based UI with Inertia.js',
                'blade' => 'Blade Only - Pure PHP/HTML views'
            ],
            'livewire'
        );

        // Set frontend in .env
        $this->setEnvironmentValue('DATA_BREACH_FRONTEND', $frontend);

        // Publish views
        $this->call('vendor:publish', ['--tag' => 'data-breach-views']);

        // Create example components based on frontend choice
        $this->createExampleComponents($frontend);

        $this->info('');
        $this->info('✅ Installation completed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Configure your API keys in config/data-breach.php');
        $this->info('2. Add the service provider to config/app.php (if not auto-discovered)');
        $this->info('3. Run migrations: php artisan migrate');
        $this->info('4. Start using the components in your views');
        $this->info('');
        $this->info('Documentation: https://docs.laravelgpt.com/data-breach');
    }

    protected function createExampleComponents(string $frontend): void
    {
        $this->info('Creating example components for ' . ucfirst($frontend) . '...');

        switch ($frontend) {
            case 'livewire':
                $this->createLivewireExample();
                break;
            case 'volt':
                $this->createVoltExample();
                break;
            case 'vue':
                $this->createVueExample();
                break;
            case 'react':
                $this->createReactExample();
                break;
            case 'blade':
                $this->createBladeExample();
                break;
        }
    }

    protected function createLivewireExample(): void
    {
        $exampleView = resource_path('views/examples/data-breach-livewire.blade.php');
        
        if (!File::exists(dirname($exampleView))) {
            File::makeDirectory(dirname($exampleView), 0755, true);
        }

        File::put($exampleView, $this->getLivewireExampleContent());
        $this->info('Created example: resources/views/examples/data-breach-livewire.blade.php');
    }

    protected function createVoltExample(): void
    {
        $exampleView = resource_path('views/examples/data-breach-volt.blade.php');
        
        if (!File::exists(dirname($exampleView))) {
            File::makeDirectory(dirname($exampleView), 0755, true);
        }

        File::put($exampleView, $this->getVoltExampleContent());
        $this->info('Created example: resources/views/examples/data-breach-volt.blade.php');
    }

    protected function createVueExample(): void
    {
        $exampleComponent = resource_path('js/components/DataBreachExample.vue');
        
        if (!File::exists(dirname($exampleComponent))) {
            File::makeDirectory(dirname($exampleComponent), 0755, true);
        }

        File::put($exampleComponent, $this->getVueExampleContent());
        $this->info('Created example: resources/js/components/DataBreachExample.vue');
    }

    protected function createReactExample(): void
    {
        $exampleComponent = resource_path('js/components/DataBreachExample.jsx');
        
        if (!File::exists(dirname($exampleComponent))) {
            File::makeDirectory(dirname($exampleComponent), 0755, true);
        }

        File::put($exampleComponent, $this->getReactExampleContent());
        $this->info('Created example: resources/js/components/DataBreachExample.jsx');
    }

    protected function createBladeExample(): void
    {
        $exampleView = resource_path('views/examples/data-breach-blade.blade.php');
        
        if (!File::exists(dirname($exampleView))) {
            File::makeDirectory(dirname($exampleView), 0755, true);
        }

        File::put($exampleView, $this->getBladeExampleContent());
        $this->info('Created example: resources/views/examples/data-breach-blade.blade.php');
    }

    protected function setEnvironmentValue(string $key, string $value): void
    {
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            $this->warn('.env file not found. Please add ' . $key . '=' . $value . ' to your .env file.');
            return;
        }

        $envContent = File::get($envFile);
        
        if (strpos($envContent, $key . '=') !== false) {
            $envContent = preg_replace('/^' . $key . '=.*$/m', $key . '=' . $value, $envContent);
        } else {
            $envContent .= "\n" . $key . '=' . $value;
        }

        File::put($envFile, $envContent);
        $this->info('Updated .env file with ' . $key . '=' . $value);
    }

    protected function getLivewireExampleContent(): string
    {
        return <<<'BLADE'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Data Breach Security Dashboard</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Password Checker -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Password Security Check</h2>
            <livewire:data-breach::password-checker />
        </div>
        
        <!-- IP Reputation Check -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">IP Reputation Check</h2>
            <livewire:data-breach::ip-checker />
        </div>
        
        <!-- Cursor Analytics -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Cursor Analytics</h2>
            <livewire:data-breach::cursor-analytics />
        </div>
        
        <!-- Session Replay -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Session Replay</h2>
            <livewire:data-breach::session-replay />
        </div>
    </div>
</div>
@endsection
BLADE;
    }

    protected function getVoltExampleContent(): string
    {
        return <<<'BLADE'
<?php

use function Livewire\Volt\{state, mount};

state(['password' => '', 'result' => null, 'isChecking' => false]);

mount(function () {
    // Component initialization
});

$checkPassword = function () {
    $this->isChecking = true;
    
    try {
        $service = app(\LaravelGPT\DataBreach\Services\PasswordBreachService::class);
        $this->result = $service->checkPassword($this->password);
    } catch (\Exception $e) {
        $this->result = ['error' => $e->getMessage()];
    } finally {
        $this->isChecking = false;
    }
};

?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold mb-4">Password Security Check (Volt)</h2>
    
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input 
                type="password" 
                wire:model="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter password to check"
            >
        </div>
        
        <button 
            wire:click="checkPassword"
            wire:loading.attr="disabled"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
            <span wire:loading.remove>Check Password</span>
            <span wire:loading>Checking...</span>
        </button>
        
        @if($result)
            <div class="mt-4 p-4 rounded-md {{ $result['compromised'] ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' }}">
                @if($result['compromised'])
                    <div class="text-red-800">
                        <strong>⚠️ Password Compromised!</strong>
                        <p class="mt-1">This password has been found in {{ $result['breach_count'] }} data breaches.</p>
                    </div>
                @else
                    <div class="text-green-800">
                        <strong>✅ Password Safe</strong>
                        <p class="mt-1">This password has not been found in known data breaches.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
BLADE;
    }

    protected function getVueExampleContent(): string
    {
        return <<<'VUE'
<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Data Breach Security (Vue.js)</h2>
        
        <div class="space-y-4">
            <!-- Password Checker -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input 
                    v-model="password"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter password to check"
                >
            </div>
            
            <button 
                @click="checkPassword"
                :disabled="isChecking"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
                {{ isChecking ? 'Checking...' : 'Check Password' }}
            </button>
            
            <!-- Result Display -->
            <div v-if="result" class="mt-4 p-4 rounded-md" :class="resultClass">
                <div v-if="result.compromised" class="text-red-800">
                    <strong>⚠️ Password Compromised!</strong>
                    <p class="mt-1">This password has been found in {{ result.breach_count }} data breaches.</p>
                </div>
                <div v-else class="text-green-800">
                    <strong>✅ Password Safe</strong>
                    <p class="mt-1">This password has not been found in known data breaches.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            password: '',
            result: null,
            isChecking: false
        }
    },
    computed: {
        resultClass() {
            if (!this.result) return ''
            return this.result.compromised 
                ? 'bg-red-50 border border-red-200' 
                : 'bg-green-50 border border-green-200'
        }
    },
    methods: {
        async checkPassword() {
            if (!this.password) return
            
            this.isChecking = true
            
            try {
                const response = await fetch('/api/data-breach/password/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ password: this.password })
                })
                
                this.result = await response.json()
            } catch (error) {
                console.error('Error checking password:', error)
                this.result = { error: 'Failed to check password' }
            } finally {
                this.isChecking = false
            }
        }
    }
}
</script>
VUE;
    }

    protected function getReactExampleContent(): string
    {
        return <<<'JSX'
import React, { useState } from 'react'

export default function DataBreachExample() {
    const [password, setPassword] = useState('')
    const [result, setResult] = useState(null)
    const [isChecking, setIsChecking] = useState(false)

    const checkPassword = async () => {
        if (!password) return
        
        setIsChecking(true)
        
        try {
            const response = await fetch('/api/data-breach/password/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password })
            })
            
            const data = await response.json()
            setResult(data)
        } catch (error) {
            console.error('Error checking password:', error)
            setResult({ error: 'Failed to check password' })
        } finally {
            setIsChecking(false)
        }
    }

    const getResultClass = () => {
        if (!result) return ''
        return result.compromised 
            ? 'bg-red-50 border border-red-200' 
            : 'bg-green-50 border border-green-200'
    }

    return (
        <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold mb-4">Data Breach Security (React)</h2>
            
            <div className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input 
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter password to check"
                    />
                </div>
                
                <button 
                    onClick={checkPassword}
                    disabled={isChecking}
                    className="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    {isChecking ? 'Checking...' : 'Check Password'}
                </button>
                
                {result && (
                    <div className={`mt-4 p-4 rounded-md ${getResultClass()}`}>
                        {result.compromised ? (
                            <div className="text-red-800">
                                <strong>⚠️ Password Compromised!</strong>
                                <p className="mt-1">This password has been found in {result.breach_count} data breaches.</p>
                            </div>
                        ) : (
                            <div className="text-green-800">
                                <strong>✅ Password Safe</strong>
                                <p className="mt-1">This password has not been found in known data breaches.</p>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    )
}
JSX;
    }

    protected function getBladeExampleContent(): string
    {
        return <<<'BLADE'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Data Breach Security Dashboard (Blade)</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Password Checker -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Password Security Check</h2>
            
            <form action="{{ route('data-breach.password.check') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input 
                        type="password" 
                        name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter password to check"
                        required
                    >
                </div>
                
                <button 
                    type="submit"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Check Password
                </button>
            </form>
            
            @if(session('password_result'))
                @php $result = session('password_result') @endphp
                <div class="mt-4 p-4 rounded-md {{ $result['compromised'] ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' }}">
                    @if($result['compromised'])
                        <div class="text-red-800">
                            <strong>⚠️ Password Compromised!</strong>
                            <p class="mt-1">This password has been found in {{ $result['breach_count'] }} data breaches.</p>
                        </div>
                    @else
                        <div class="text-green-800">
                            <strong>✅ Password Safe</strong>
                            <p class="mt-1">This password has not been found in known data breaches.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- IP Checker -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">IP Reputation Check</h2>
            
            <form action="{{ route('data-breach.ip.check') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">IP Address</label>
                    <input 
                        type="text" 
                        name="ip"
                        value="{{ request()->ip() }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter IP address to check"
                        required
                    >
                </div>
                
                <button 
                    type="submit"
                    class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                >
                    Check IP
                </button>
            </form>
            
            @if(session('ip_result'))
                @php $result = session('ip_result') @endphp
                <div class="mt-4 p-4 rounded-md {{ $result['suspicious'] ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' }}">
                    @if($result['suspicious'])
                        <div class="text-red-800">
                            <strong>⚠️ Suspicious IP!</strong>
                            <p class="mt-1">This IP address has been flagged as suspicious.</p>
                        </div>
                    @else
                        <div class="text-green-800">
                            <strong>✅ IP Safe</strong>
                            <p class="mt-1">This IP address appears to be safe.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
BLADE;
    }
} 