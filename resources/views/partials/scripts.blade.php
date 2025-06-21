<!-- LaravelGPT Data Breach Scripts -->
<script>
    // Data Breach Analytics Configuration
    window.DataBreachConfig = {
        trackingEnabled: {{ config('data-breach.cursor.tracking_enabled', true) ? 'true' : 'false' }},
        sessionLogging: {{ config('data-breach.cursor.session_logging', true) ? 'true' : 'false' }},
        analyticsEnabled: {{ config('data-breach.cursor.analytics_enabled', true) ? 'true' : 'false' }},
        aiUxFeedback: {{ config('data-breach.cursor.ai_ux_feedback', true) ? 'true' : 'false' }},
        bugReportEnrichment: {{ config('data-breach.cursor.bug_report_enrichment', true) ? 'true' : 'false' }},
        apiBaseUrl: '{{ route("data-breach.cursor.track") }}',
        csrfToken: '{{ csrf_token() }}'
    };

    // Cursor Tracking Utility
    window.DataBreachCursor = {
        isTracking: false,
        lastX: 0,
        lastY: 0,
        lastTime: Date.now(),
        sessionKey: '{{ session()->getId() }}',

        startTracking() {
            this.isTracking = true;
            console.log('Data Breach: Cursor tracking started');
        },

        stopTracking() {
            this.isTracking = false;
            console.log('Data Breach: Cursor tracking stopped');
        },

        trackEvent(x, y, eventType, elementId = null, elementType = null, velocity = 0) {
            if (!this.isTracking || !window.DataBreachConfig.trackingEnabled) return;

            fetch(window.DataBreachConfig.apiBaseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.DataBreachConfig.csrfToken
                },
                body: JSON.stringify({
                    x: x,
                    y: y,
                    event_type: eventType,
                    element_id: elementId,
                    element_type: elementType,
                    velocity: velocity
                })
            }).catch(error => {
                console.warn('Data Breach: Failed to track cursor event', error);
            });
        },

        calculateVelocity(currentX, currentY) {
            const currentTime = Date.now();
            const timeDiff = currentTime - this.lastTime;
            const distance = Math.sqrt(
                Math.pow(currentX - this.lastX, 2) + 
                Math.pow(currentY - this.lastY, 2)
            );
            const velocity = timeDiff > 0 ? distance / timeDiff * 1000 : 0;

            this.lastX = currentX;
            this.lastY = currentY;
            this.lastTime = currentTime;

            return velocity;
        }
    };

    // Session Replay Utility
    window.DataBreachSession = {
        isRecording: false,
        sessionKey: null,

        async startRecording() {
            try {
                const response = await fetch('{{ route("data-breach.session.start") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.DataBreachConfig.csrfToken
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.sessionKey = data.session_key;
                    this.isRecording = true;
                    console.log('Data Breach: Session recording started', this.sessionKey);
                }
            } catch (error) {
                console.warn('Data Breach: Failed to start session recording', error);
            }
        },

        async stopRecording() {
            if (!this.isRecording || !this.sessionKey) return;

            try {
                const response = await fetch('{{ route("data-breach.session.stop") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.DataBreachConfig.csrfToken
                    },
                    body: JSON.stringify({
                        session_key: this.sessionKey
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.isRecording = false;
                    console.log('Data Breach: Session recording stopped', data.data);
                }
            } catch (error) {
                console.warn('Data Breach: Failed to stop session recording', error);
            }
        },

        async recordEvent(type, data, screenshot = null) {
            if (!this.isRecording || !this.sessionKey) return;

            try {
                await fetch('{{ route("data-breach.session.record") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.DataBreachConfig.csrfToken
                    },
                    body: JSON.stringify({
                        session_key: this.sessionKey,
                        type: type,
                        data: data,
                        screenshot: screenshot
                    })
                });
            } catch (error) {
                console.warn('Data Breach: Failed to record session event', error);
            }
        }
    };

    // Initialize cursor tracking if enabled
    document.addEventListener('DOMContentLoaded', function() {
        if (window.DataBreachConfig.trackingEnabled) {
            // Track mouse movements
            document.addEventListener('mousemove', function(e) {
                const velocity = window.DataBreachCursor.calculateVelocity(e.clientX, e.clientY);
                window.DataBreachCursor.trackEvent(e.clientX, e.clientY, 'move', null, null, velocity);
            });

            // Track clicks
            document.addEventListener('click', function(e) {
                const elementId = e.target.id || e.target.className || null;
                const elementType = e.target.tagName.toLowerCase();
                window.DataBreachCursor.trackEvent(e.clientX, e.clientY, 'click', elementId, elementType, 0);
            });

            // Track hovers
            document.addEventListener('mouseover', function(e) {
                const elementId = e.target.id || e.target.className || null;
                const elementType = e.target.tagName.toLowerCase();
                window.DataBreachCursor.trackEvent(e.clientX, e.clientY, 'hover', elementId, elementType, 0);
            });
        }

        // Initialize session recording if enabled
        if (window.DataBreachConfig.sessionLogging) {
            window.DataBreachSession.startRecording();

            // Record page load
            window.DataBreachSession.recordEvent('page_load', {
                url: window.location.href,
                title: document.title,
                userAgent: navigator.userAgent,
                screenResolution: `${screen.width}x${screen.height}`,
                viewportSize: `${window.innerWidth}x${window.innerHeight}`
            });

            // Record form interactions
            document.addEventListener('input', function(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                    window.DataBreachSession.recordEvent('form_input', {
                        elementId: e.target.id || null,
                        elementType: e.target.tagName.toLowerCase(),
                        elementName: e.target.name || null,
                        valueLength: e.target.value?.length || 0,
                        url: window.location.href
                    });
                }
            });

            // Record form submissions
            document.addEventListener('submit', function(e) {
                window.DataBreachSession.recordEvent('form_submit', {
                    formId: e.target.id || null,
                    formAction: e.target.action || null,
                    formMethod: e.target.method || null,
                    url: window.location.href
                });
            });

            // Record errors
            window.addEventListener('error', function(e) {
                window.DataBreachSession.recordEvent('error', {
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno,
                    url: window.location.href
                });
            });

            // Stop recording when page unloads
            window.addEventListener('beforeunload', function() {
                window.DataBreachSession.stopRecording();
            });
        }
    });
</script> 