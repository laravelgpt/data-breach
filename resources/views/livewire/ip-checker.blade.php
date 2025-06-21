<div class="space-y-4">
    <div class="flex space-x-4">
        <input 
            type="text" 
            wire:model="ipAddress" 
            placeholder="Enter IP address (e.g., 8.8.8.8)"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <button 
            wire:click="checkIp" 
            wire:loading.attr="disabled"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
            <span wire:loading.remove>Check IP</span>
            <span wire:loading>Checking...</span>
        </button>
    </div>

    @if($error)
        <div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
            {{ $error }}
        </div>
    @endif

    @if($isLoading)
        <div class="flex items-center justify-center p-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
    @endif

    @if($result)
        <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-3">
            <h3 class="text-lg font-semibold text-gray-900">IP Reputation Results</h3>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">IP Address:</span>
                    <span class="text-gray-900">{{ $result['ip'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Country:</span>
                    <span class="text-gray-900">{{ $result['country'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">City:</span>
                    <span class="text-gray-900">{{ $result['city'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">ISP:</span>
                    <span class="text-gray-900">{{ $result['isp'] ?? 'N/A' }}</span>
                </div>
            </div>

            @if(isset($result['threat_score']))
                <div class="mt-4">
                    <span class="font-medium text-gray-700">Threat Score:</span>
                    <span class="ml-2 px-2 py-1 rounded text-sm font-medium 
                        {{ $result['threat_score'] > 80 ? 'bg-red-100 text-red-800' : 
                           ($result['threat_score'] > 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                        {{ $result['threat_score'] }}%
                    </span>
                </div>
            @endif

            @if(isset($result['flags']))
                <div class="mt-4">
                    <span class="font-medium text-gray-700">Flags:</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($result['flags'] as $flag => $value)
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                {{ ucfirst($flag) }}: {{ $value ? 'Yes' : 'No' }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($result['recommendation']))
                <div class="mt-4 p-3 rounded-md 
                    {{ $result['recommendation'] === 'block' ? 'bg-red-100 text-red-700' : 
                       ($result['recommendation'] === 'monitor' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                    <strong>Recommendation:</strong> {{ ucfirst($result['recommendation']) }}
                </div>
            @endif
        </div>
    @endif
</div> 