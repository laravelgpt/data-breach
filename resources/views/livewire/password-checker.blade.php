<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">🔐 Password Security Checker</h2>
        <p class="text-gray-600">Check if your password has been compromised and analyze its strength</p>
    </div>

    <!-- Password Input -->
    <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Enter Password to Check
        </label>
        <div class="flex gap-2">
            <input 
                type="password" 
                id="password"
                wire:model.live="password" 
                wire:keydown.enter="checkPassword"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter your password..."
            >
            <button 
                wire:click="checkPassword"
                wire:loading.attr="disabled"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
            >
                <span wire:loading.remove>🔍 Check</span>
                <span wire:loading>Checking...</span>
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div wire:loading class="mb-6">
        <div class="flex items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Checking password security...</span>
        </div>
    </div>

    <!-- Results -->
    <div wire:loading.remove>
        @if(!empty($password) && !empty($breachResult))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Breach Status -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <span class="text-2xl mr-3">{{ $this->getBreachStatusIcon() }}</span>
                        <h3 class="text-xl font-semibold {{ $this->getBreachStatusColor() }}">
                            {{ $breachResult['compromised'] ? 'Password Compromised' : 'Password Safe' }}
                        </h3>
                    </div>
                    
                    @if($breachResult['compromised'])
                        <div class="space-y-2">
                            <p class="text-red-600 font-medium">
                                This password has been found in {{ $breachResult['breach_count'] }} data breaches
                            </p>
                            <p class="text-sm text-gray-600">
                                Sources: {{ implode(', ', $breachResult['sources']) }}
                            </p>
                        </div>
                    @else
                        <p class="text-green-600 font-medium">
                            This password has not been found in any known data breaches
                        </p>
                    @endif
                </div>

                <!-- Strength Analysis -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Password Strength</h3>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span>Strength Level</span>
                            <span class="{{ $this->getStrengthColor() }} font-medium">
                                {{ ucfirst(str_replace('_', ' ', $strengthResult['level'])) }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="h-2 rounded-full {{ $this->getStrengthBgColor() }} transition-all duration-300"
                                style="width: {{ min(100, ($strengthResult['score'] / 8) * 100) }}%"
                            ></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Score: {{ $strengthResult['score'] }}/8</p>
                    </div>

                    @if(!empty($strengthResult['feedback']))
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-gray-700">Suggestions:</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                @foreach($strengthResult['feedback'] as $feedback)
                                    <li class="flex items-start">
                                        <span class="text-yellow-500 mr-2">•</span>
                                        {{ $feedback }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recommendations -->
            @if(!empty($recommendations))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">🔒 Security Recommendations</h3>
                    <ul class="space-y-2">
                        @foreach($recommendations as $recommendation)
                            <li class="flex items-start text-blue-800">
                                <span class="text-blue-500 mr-2">•</span>
                                {{ $recommendation }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    </div>

    <!-- Passkey Generator -->
    <div class="border-t pt-6">
        <h3 class="text-xl font-semibold mb-4">🔑 Generate Secure Passkeys</h3>
        
        <div class="flex gap-3 mb-4">
            <button 
                wire:click="generatePasskey"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500"
            >
                Generate Passkey
            </button>
            <button 
                wire:click="generatePassphrase"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500"
            >
                Generate Passphrase
            </button>
        </div>

        @if($showPasskey && !empty($generatedPasskey))
            <div class="bg-gray-50 rounded-lg p-6">
                <h4 class="font-semibold mb-3">Generated {{ isset($generatedPasskey['passkey']) ? 'Passkey' : 'Passphrase' }}</h4>
                
                <div class="flex items-center gap-3 mb-3">
                    <input 
                        type="text" 
                        value="{{ $generatedPasskey['passkey'] ?? $generatedPasskey['passphrase'] }}" 
                        readonly
                        class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm"
                    >
                    <button 
                        wire:click="copyToClipboard('{{ $generatedPasskey['passkey'] ?? $generatedPasskey['passphrase'] }}')"
                        class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                    >
                        📋 Copy
                    </button>
                </div>

                @if(isset($generatedPasskey['strength']))
                    <div class="text-sm text-gray-600">
                        <p>Strength: <span class="font-medium">{{ ucfirst($generatedPasskey['strength']['level']) }}</span></p>
                        <p>Entropy: <span class="font-medium">{{ number_format($generatedPasskey['strength']['entropy'], 1) }} bits</span></p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('copy-to-clipboard', (event) => {
        navigator.clipboard.writeText(event.text).then(() => {
            console.log('Copied to clipboard');
        });
    });
});
</script> 