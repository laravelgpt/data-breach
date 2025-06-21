<!-- LaravelGPT Data Breach Cursor Tracking -->
@if(config('data-breach.cursor.tracking_enabled', true))
<div id="data-breach-cursor-tracking" class="hidden">
    <!-- Hidden tracking element -->
    <div class="data-breach-cursor-tracker" 
         data-session-key="{{ session()->getId() }}"
         data-user-id="{{ auth()->id() }}"
         data-tenant-id="{{ auth()->user()?->tenant_id }}"
         data-tracking-enabled="{{ config('data-breach.cursor.tracking_enabled', true) ? 'true' : 'false' }}"
         data-analytics-enabled="{{ config('data-breach.cursor.analytics_enabled', true) ? 'true' : 'false' }}"
         data-ai-ux-feedback="{{ config('data-breach.cursor.ai_ux_feedback', true) ? 'true' : 'false' }}">
    </div>

    <script>
        // Cursor Tracking Initialization
        document.addEventListener('DOMContentLoaded', function() {
            const tracker = document.querySelector('.data-breach-cursor-tracker');
            if (!tracker) return;

            const sessionKey = tracker.dataset.sessionKey;
            const userId = tracker.dataset.userId;
            const tenantId = tracker.dataset.tenantId;
            const trackingEnabled = tracker.dataset.trackingEnabled === 'true';
            const analyticsEnabled = tracker.dataset.analyticsEnabled === 'true';
            const aiUxFeedback = tracker.dataset.aiUxFeedback === 'true';

            if (!trackingEnabled) return;

            let lastX = 0, lastY = 0;
            let lastTime = Date.now();
            let isTracking = false;

            // Start tracking when user interacts
            function startTracking() {
                if (isTracking) return;
                isTracking = true;
                console.log('Data Breach: Cursor tracking started for session', sessionKey);
            }

            // Track mouse movement with debouncing
            let moveTimeout;
            document.addEventListener('mousemove', function(e) {
                if (!isTracking) {
                    startTracking();
                }

                clearTimeout(moveTimeout);
                moveTimeout = setTimeout(() => {
                    const currentTime = Date.now();
                    const timeDiff = currentTime - lastTime;
                    const distance = Math.sqrt(
                        Math.pow(e.clientX - lastX, 2) + 
                        Math.pow(e.clientY - lastY, 2)
                    );
                    const velocity = timeDiff > 0 ? distance / timeDiff * 1000 : 0;

                    // Send cursor event
                    fetch('{{ route("data-breach.cursor.track") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            x: e.clientX,
                            y: e.clientY,
                            event_type: 'move',
                            element_id: null,
                            element_type: null,
                            velocity: velocity
                        })
                    }).catch(error => {
                        console.warn('Data Breach: Failed to track cursor movement', error);
                    });

                    lastX = e.clientX;
                    lastY = e.clientY;
                    lastTime = currentTime;
                }, 50); // 50ms debounce
            });

            // Track clicks
            document.addEventListener('click', function(e) {
                if (!isTracking) {
                    startTracking();
                }

                const elementId = e.target.id || e.target.className || null;
                const elementType = e.target.tagName.toLowerCase();

                fetch('{{ route("data-breach.cursor.track") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        x: e.clientX,
                        y: e.clientY,
                        event_type: 'click',
                        element_id: elementId,
                        element_type: elementType,
                        velocity: 0
                    })
                }).catch(error => {
                    console.warn('Data Breach: Failed to track click event', error);
                });
            });

            // Track hovers with debouncing
            let hoverTimeout;
            document.addEventListener('mouseover', function(e) {
                if (!isTracking) {
                    startTracking();
                }

                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    const elementId = e.target.id || e.target.className || null;
                    const elementType = e.target.tagName.toLowerCase();

                    fetch('{{ route("data-breach.cursor.track") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            x: e.clientX,
                            y: e.clientY,
                            event_type: 'hover',
                            element_id: elementId,
                            element_type: elementType,
                            velocity: 0
                        })
                    }).catch(error => {
                        console.warn('Data Breach: Failed to track hover event', error);
                    });
                }, 100); // 100ms debounce for hover
            });

            // Track scroll events
            let scrollTimeout;
            document.addEventListener('scroll', function(e) {
                if (!isTracking) {
                    startTracking();
                }

                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    fetch('{{ route("data-breach.cursor.track") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            x: 0,
                            y: 0,
                            event_type: 'scroll',
                            element_id: null,
                            element_type: null,
                            velocity: 0,
                            scroll_data: {
                                scrollX: window.scrollX,
                                scrollY: window.scrollY,
                                scrollTop: document.documentElement.scrollTop || document.body.scrollTop
                            }
                        })
                    }).catch(error => {
                        console.warn('Data Breach: Failed to track scroll event', error);
                    });
                }, 150); // 150ms debounce for scroll
            });

            // Track form interactions
            document.addEventListener('input', function(e) {
                if (!isTracking) {
                    startTracking();
                }

                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                    fetch('{{ route("data-breach.cursor.track") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            x: 0,
                            y: 0,
                            event_type: 'form_input',
                            element_id: e.target.id || null,
                            element_type: e.target.tagName.toLowerCase(),
                            velocity: 0,
                            form_data: {
                                element_name: e.target.name || null,
                                value_length: e.target.value?.length || 0,
                                input_type: e.target.type || 'text'
                            }
                        })
                    }).catch(error => {
                        console.warn('Data Breach: Failed to track form input', error);
                    });
                }
            });

            // Track page visibility changes
            document.addEventListener('visibilitychange', function() {
                if (!isTracking) return;

                fetch('{{ route("data-breach.cursor.track") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        x: 0,
                        y: 0,
                        event_type: 'visibility_change',
                        element_id: null,
                        element_type: null,
                        velocity: 0,
                        visibility_data: {
                            hidden: document.hidden,
                            visibility_state: document.visibilityState
                        }
                    })
                }).catch(error => {
                    console.warn('Data Breach: Failed to track visibility change', error);
                });
            });

            // Track page unload
            window.addEventListener('beforeunload', function() {
                if (!isTracking) return;

                // Use sendBeacon for reliable delivery during page unload
                const data = JSON.stringify({
                    x: 0,
                    y: 0,
                    event_type: 'page_unload',
                    element_id: null,
                    element_type: null,
                    velocity: 0,
                    session_data: {
                        session_key: sessionKey,
                        user_id: userId,
                        tenant_id: tenantId,
                        duration: Date.now() - lastTime
                    }
                });

                navigator.sendBeacon('{{ route("data-breach.cursor.track") }}', data);
            });

            // Auto-hide cursor on inactivity (optional)
            let inactivityTimeout;
            function resetInactivityTimer() {
                clearTimeout(inactivityTimeout);
                inactivityTimeout = setTimeout(() => {
                    if (isTracking) {
                        fetch('{{ route("data-breach.cursor.track") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                x: 0,
                                y: 0,
                                event_type: 'inactivity',
                                element_id: null,
                                element_type: null,
                                velocity: 0
                            })
                        }).catch(error => {
                            console.warn('Data Breach: Failed to track inactivity', error);
                        });
                    }
                }, 60000); // 1 minute of inactivity
            }

            // Reset timer on any interaction
            document.addEventListener('mousemove', resetInactivityTimer);
            document.addEventListener('click', resetInactivityTimer);
            document.addEventListener('keypress', resetInactivityTimer);

            // Start inactivity timer
            resetInactivityTimer();
        });
    </script>
</div>
@endif 