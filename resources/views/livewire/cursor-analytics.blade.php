<div class="cursor-analytics-container">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Cursor Analytics</h2>
            <div class="flex space-x-2">
                <button 
                    wire:click="startTracking"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                    {{ $isTracking ? 'disabled' : '' }}
                >
                    Start Tracking
                </button>
                <button 
                    wire:click="stopTracking"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                    {{ !$isTracking ? 'disabled' : '' }}
                >
                    Stop Tracking
                </button>
            </div>
        </div>

        <!-- Status Indicator -->
        <div class="mb-6">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full {{ $isTracking ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                <span class="text-sm font-medium">
                    {{ $isTracking ? 'Tracking Active' : 'Tracking Inactive' }}
                </span>
            </div>
        </div>

        <!-- Analytics Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800">Total Moves</h3>
                <p class="text-2xl font-bold text-blue-900">{{ $analytics['total_moves'] ?? 0 }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-green-800">Clicks</h3>
                <p class="text-2xl font-bold text-green-900">{{ $analytics['clicks'] ?? 0 }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-yellow-800">Fast Movements</h3>
                <p class="text-2xl font-bold text-yellow-900">{{ $analytics['fast_movements'] ?? 0 }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-purple-800">Slow Movements</h3>
                <p class="text-2xl font-bold text-purple-900">{{ $analytics['slow_movements'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Hover Elements -->
        @if(!empty($analytics['hover_elements']))
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Most Hovered Elements</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                @foreach(array_slice($analytics['hover_elements'], 0, 5) as $elementId => $count)
                <div class="flex justify-between items-center py-1">
                    <span class="text-sm text-gray-700">{{ $elementId }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ $count }} hovers</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Events -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Recent Events</h3>
                <button wire:click="getEvents" class="text-sm text-blue-600 hover:text-blue-800">
                    Refresh
                </button>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg max-h-64 overflow-y-auto">
                @forelse(array_slice($events, -10) as $event)
                <div class="flex items-center space-x-3 py-2 border-b border-gray-200 last:border-b-0">
                    <div class="w-2 h-2 rounded-full 
                        {{ $event['event_type'] === 'click' ? 'bg-red-500' : 
                           ($event['event_type'] === 'hover' ? 'bg-blue-500' : 'bg-gray-500') }}">
                    </div>
                    <span class="text-sm text-gray-600">{{ $event['event_type'] }}</span>
                    <span class="text-sm text-gray-500">({{ $event['x'] }}, {{ $event['y'] }})</span>
                    <span class="text-xs text-gray-400">{{ $event['timestamp'] ?? '' }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No events recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-4">
            <button 
                wire:click="getAnalytics"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                Refresh Analytics
            </button>
            <button 
                wire:click="archiveData"
                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
            >
                Archive Data
            </button>
        </div>
    </div>

    <!-- JavaScript for cursor tracking -->
    <script>
        document.addEventListener('livewire:init', () => {
            let isTracking = false;
            let lastX = 0, lastY = 0;
            let lastTime = Date.now();

            Livewire.on('cursor-tracking-started', () => {
                isTracking = true;
                console.log('Cursor tracking started');
            });

            Livewire.on('cursor-tracking-stopped', () => {
                isTracking = false;
                console.log('Cursor tracking stopped');
            });

            // Track mouse movements
            document.addEventListener('mousemove', (e) => {
                if (!isTracking) return;

                const currentTime = Date.now();
                const timeDiff = currentTime - lastTime;
                const distance = Math.sqrt(Math.pow(e.clientX - lastX, 2) + Math.pow(e.clientY - lastY, 2));
                const velocity = timeDiff > 0 ? distance / timeDiff * 1000 : 0;

                @this.trackEvent(e.clientX, e.clientY, 'move', null, null, velocity);

                lastX = e.clientX;
                lastY = e.clientY;
                lastTime = currentTime;
            });

            // Track clicks
            document.addEventListener('click', (e) => {
                if (!isTracking) return;

                const elementId = e.target.id || e.target.className || null;
                const elementType = e.target.tagName.toLowerCase();

                @this.trackEvent(e.clientX, e.clientY, 'click', elementId, elementType, 0);
            });

            // Track hovers
            document.addEventListener('mouseover', (e) => {
                if (!isTracking) return;

                const elementId = e.target.id || e.target.className || null;
                const elementType = e.target.tagName.toLowerCase();

                @this.trackEvent(e.clientX, e.clientY, 'hover', elementId, elementType, 0);
            });
        });
    </script>
</div> 