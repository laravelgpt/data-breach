<div class="session-replay-container">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Session Replay</h2>
            <div class="flex space-x-2">
                <button 
                    wire:click="startRecording"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                    {{ $isRecording ? 'disabled' : '' }}
                >
                    Start Recording
                </button>
                <button 
                    wire:click="stopRecording"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                    {{ !$isRecording ? 'disabled' : '' }}
                >
                    Stop Recording
                </button>
            </div>
        </div>

        <!-- Status Indicator -->
        <div class="mb-6">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full 
                    {{ $status === 'recording' ? 'bg-red-500' : 
                       ($status === 'completed' ? 'bg-green-500' : 'bg-gray-400') }}">
                </div>
                <span class="text-sm font-medium">
                    {{ ucfirst($status) }}
                </span>
                @if($isRecording)
                <span class="text-xs text-red-600 animate-pulse">● RECORDING</span>
                @endif
            </div>
        </div>

        <!-- Session Key -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Session Key</label>
            <div class="flex items-center space-x-2">
                <input 
                    type="text" 
                    value="{{ $sessionKey }}" 
                    readonly 
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm"
                >
                <button 
                    onclick="navigator.clipboard.writeText('{{ $sessionKey }}')"
                    class="px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm"
                >
                    Copy
                </button>
            </div>
        </div>

        <!-- Session Analytics -->
        @if(!empty($analytics))
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Session Analytics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800">Duration</h4>
                    <p class="text-xl font-bold text-blue-900">{{ $analytics['duration'] ?? 0 }}s</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-green-800">Events</h4>
                    <p class="text-xl font-bold text-green-900">{{ $analytics['events_count'] ?? 0 }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-purple-800">Device</h4>
                    <p class="text-xl font-bold text-purple-900">{{ ucfirst($analytics['device_type'] ?? 'Unknown') }}</p>
                </div>
            </div>

            @if(!empty($analytics['metadata']))
            <div class="mt-4">
                <h4 class="text-md font-semibold mb-2">Event Breakdown</h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                    <div class="bg-gray-50 p-2 rounded">
                        <span class="text-gray-600">Page Loads:</span>
                        <span class="font-medium">{{ $analytics['metadata']['page_loads'] ?? 0 }}</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded">
                        <span class="text-gray-600">Clicks:</span>
                        <span class="font-medium">{{ $analytics['metadata']['clicks'] ?? 0 }}</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded">
                        <span class="text-gray-600">Scrolls:</span>
                        <span class="font-medium">{{ $analytics['metadata']['scrolls'] ?? 0 }}</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded">
                        <span class="text-gray-600">Form Interactions:</span>
                        <span class="font-medium">{{ $analytics['metadata']['form_interactions'] ?? 0 }}</span>
                    </div>
                    <div class="bg-gray-50 p-2 rounded">
                        <span class="text-gray-600">Errors:</span>
                        <span class="font-medium">{{ $analytics['metadata']['errors'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Session Events -->
        @if(!empty($sessionData['events']))
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Session Events</h3>
                <span class="text-sm text-gray-500">{{ count($sessionData['events']) }} events</span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg max-h-64 overflow-y-auto">
                @foreach(array_slice($sessionData['events'], -20) as $event)
                <div class="flex items-center space-x-3 py-2 border-b border-gray-200 last:border-b-0">
                    <div class="w-2 h-2 rounded-full 
                        {{ $event['type'] === 'click' ? 'bg-red-500' : 
                           ($event['type'] === 'page_load' ? 'bg-blue-500' : 
                           ($event['type'] === 'error' ? 'bg-yellow-500' : 'bg-gray-500')) }}">
                    </div>
                    <span class="text-sm font-medium text-gray-700">{{ $event['type'] }}</span>
                    <span class="text-sm text-gray-500">{{ $event['timestamp'] ?? '' }}</span>
                    @if(!empty($event['data']))
                    <span class="text-xs text-gray-400">{{ json_encode($event['data']) }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex space-x-4">
            <button 
                wire:click="getReplay"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                Load Session
            </button>
            <button 
                wire:click="getAnalytics"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
            >
                Load Analytics
            </button>
            <button 
                wire:click="cleanup"
                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
            >
                Cleanup Old Sessions
            </button>
        </div>
    </div>

    <!-- JavaScript for session recording -->
    <script>
        document.addEventListener('livewire:init', () => {
            let isRecording = false;

            Livewire.on('session-recording-started', (event) => {
                isRecording = true;
                console.log('Session recording started:', event.sessionKey);
                
                // Record page load event
                @this.recordEvent('page_load', {
                    url: window.location.href,
                    title: document.title,
                    userAgent: navigator.userAgent,
                    screenResolution: `${screen.width}x${screen.height}`,
                    viewportSize: `${window.innerWidth}x${window.innerHeight}`
                });
            });

            Livewire.on('session-recording-stopped', () => {
                isRecording = false;
                console.log('Session recording stopped');
            });

            // Record clicks
            document.addEventListener('click', (e) => {
                if (!isRecording) return;

                @this.recordEvent('click', {
                    x: e.clientX,
                    y: e.clientY,
                    elementId: e.target.id || null,
                    elementType: e.target.tagName.toLowerCase(),
                    elementText: e.target.textContent?.substring(0, 50) || null,
                    url: window.location.href
                });
            });

            // Record scrolls
            let scrollTimeout;
            document.addEventListener('scroll', (e) => {
                if (!isRecording) return;

                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    @this.recordEvent('scroll', {
                        scrollX: window.scrollX,
                        scrollY: window.scrollY,
                        url: window.location.href
                    });
                }, 100);
            });

            // Record form interactions
            document.addEventListener('input', (e) => {
                if (!isRecording) return;

                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                    @this.recordEvent('form_input', {
                        elementId: e.target.id || null,
                        elementType: e.target.tagName.toLowerCase(),
                        elementName: e.target.name || null,
                        valueLength: e.target.value?.length || 0,
                        url: window.location.href
                    });
                }
            });

            // Record form submissions
            document.addEventListener('submit', (e) => {
                if (!isRecording) return;

                @this.recordEvent('form_submit', {
                    formId: e.target.id || null,
                    formAction: e.target.action || null,
                    formMethod: e.target.method || null,
                    url: window.location.href
                });
            });

            // Record errors
            window.addEventListener('error', (e) => {
                if (!isRecording) return;

                @this.recordEvent('error', {
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno,
                    url: window.location.href
                });
            });
        });
    </script>
</div> 