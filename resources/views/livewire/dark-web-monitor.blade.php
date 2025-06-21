<div class="space-y-4">
    <!-- Search Type Toggle -->
    <div class="flex space-x-4 mb-4">
        <label class="flex items-center">
            <input type="radio" wire:model="searchType" value="email" class="mr-2">
            <span>Email Search</span>
        </label>
        <label class="flex items-center">
            <input type="radio" wire:model="searchType" value="domain" class="mr-2">
            <span>Domain Search</span>
        </label>
    </div>

    <!-- Email Search Section -->
    @if($searchType === 'email')
        <div class="space-y-4">
            <div class="flex space-x-4">
                <input 
                    type="email" 
                    wire:model="email" 
                    placeholder="Enter email address to search"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                >
                <button 
                    wire:click="searchEmail" 
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50"
                >
                    <span wire:loading.remove>Search Email</span>
                    <span wire:loading>Searching...</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Domain Search Section -->
    @if($searchType === 'domain')
        <div class="space-y-4">
            <div class="flex space-x-4">
                <input 
                    type="text" 
                    wire:model="domain" 
                    placeholder="Enter domain name to search"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                >
                <button 
                    wire:click="searchDomain" 
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50"
                >
                    <span wire:loading.remove>Search Domain</span>
                    <span wire:loading>Searching...</span>
                </button>
            </div>
        </div>
    @endif

    @if($error)
        <div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
            {{ $error }}
        </div>
    @endif

    @if($isLoading)
        <div class="flex items-center justify-center p-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
        </div>
    @endif

    @if($result)
        <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-3">
            <h3 class="text-lg font-semibold text-gray-900">Dark Web Search Results</h3>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Search Type:</span>
                    <span class="text-gray-900">{{ ucfirst($searchType) }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Target:</span>
                    <span class="text-gray-900">{{ $searchType === 'email' ? $email : $domain }}</span>
                </div>
            </div>

            @if(isset($result['breach_count']))
                <div class="mt-4">
                    <span class="font-medium text-gray-700">Breach Count:</span>
                    <span class="ml-2 px-2 py-1 rounded text-sm font-medium 
                        {{ $result['breach_count'] > 10 ? 'bg-red-100 text-red-800' : 
                           ($result['breach_count'] > 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                        {{ $result['breach_count'] }} breaches found
                    </span>
                </div>
            @endif

            @if(isset($result['last_breach_date']))
                <div class="mt-4">
                    <span class="font-medium text-gray-700">Last Breach:</span>
                    <span class="text-gray-900">{{ $result['last_breach_date'] }}</span>
                </div>
            @endif

            @if(isset($result['breaches']))
                <div class="mt-4">
                    <span class="font-medium text-gray-700">Breach Details:</span>
                    <div class="mt-2 space-y-2 max-h-60 overflow-y-auto">
                        @foreach($result['breaches'] as $breach)
                            <div class="p-3 bg-gray-50 rounded-md">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $breach['name'] ?? 'Unknown Breach' }}</h4>
                                        @if(isset($breach['date']))
                                            <p class="text-sm text-gray-600">Date: {{ $breach['date'] }}</p>
                                        @endif
                                        @if(isset($breach['description']))
                                            <p class="text-sm text-gray-600 mt-1">{{ $breach['description'] }}</p>
                                        @endif
                                    </div>
                                    @if(isset($breach['severity']))
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $breach['severity'] === 'high' ? 'bg-red-100 text-red-800' : 
                                               ($breach['severity'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($breach['severity']) }}
                                        </span>
                                    @endif
                                </div>
                                @if(isset($breach['compromised_data']))
                                    <div class="mt-2">
                                        <span class="text-xs text-gray-500">Compromised Data:</span>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($breach['compromised_data'] as $dataType)
                                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                                                    {{ ucfirst($dataType) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($result['recommendation']))
                <div class="mt-4 p-3 rounded-md 
                    {{ $result['recommendation'] === 'immediate_action' ? 'bg-red-100 text-red-700' : 
                       ($result['recommendation'] === 'monitor' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                    <strong>Recommendation:</strong> {{ ucfirst(str_replace('_', ' ', $result['recommendation'])) }}
                </div>
            @endif

            @if(isset($result['action_items']))
                <div class="mt-4">
                    <span class="font-medium text-gray-700">Recommended Actions:</span>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        @foreach($result['action_items'] as $action)
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span>{{ $action }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</div> 