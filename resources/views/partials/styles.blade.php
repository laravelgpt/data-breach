<!-- LaravelGPT Data Breach Styles -->
<style>
    /* Data Breach Component Styles */
    .data-breach-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Cursor Analytics Styles */
    .cursor-analytics-container {
        --primary-color: #3b82f6;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
    }

    /* Session Replay Styles */
    .session-replay-container {
        --primary-color: #3b82f6;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
    }

    /* Animation Classes */
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    /* Custom Button Styles */
    .data-breach-btn {
        @apply px-4 py-2 rounded-md font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2;
    }

    .data-breach-btn-primary {
        @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
    }

    .data-breach-btn-success {
        @apply bg-green-600 text-white hover:bg-green-700 focus:ring-green-500;
    }

    .data-breach-btn-danger {
        @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
    }

    .data-breach-btn-secondary {
        @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
    }

    /* Card Styles */
    .data-breach-card {
        @apply bg-white rounded-lg shadow-md p-6 border border-gray-200;
    }

    .data-breach-card-header {
        @apply flex items-center justify-between mb-6;
    }

    .data-breach-card-title {
        @apply text-2xl font-bold text-gray-900;
    }

    /* Status Indicator Styles */
    .data-breach-status {
        @apply flex items-center space-x-2;
    }

    .data-breach-status-dot {
        @apply w-3 h-3 rounded-full;
    }

    .data-breach-status-dot-active {
        @apply bg-green-500;
    }

    .data-breach-status-dot-inactive {
        @apply bg-gray-400;
    }

    .data-breach-status-dot-recording {
        @apply bg-red-500;
    }

    /* Analytics Grid Styles */
    .data-breach-analytics-grid {
        @apply grid grid-cols-1 md:grid-cols-4 gap-4 mb-6;
    }

    .data-breach-analytics-item {
        @apply p-4 rounded-lg;
    }

    .data-breach-analytics-item-blue {
        @apply bg-blue-50;
    }

    .data-breach-analytics-item-green {
        @apply bg-green-50;
    }

    .data-breach-analytics-item-yellow {
        @apply bg-yellow-50;
    }

    .data-breach-analytics-item-purple {
        @apply bg-purple-50;
    }

    .data-breach-analytics-label {
        @apply text-sm font-medium;
    }

    .data-breach-analytics-value {
        @apply text-2xl font-bold;
    }

    /* Event List Styles */
    .data-breach-event-list {
        @apply bg-gray-50 p-4 rounded-lg max-h-64 overflow-y-auto;
    }

    .data-breach-event-item {
        @apply flex items-center space-x-3 py-2 border-b border-gray-200 last:border-b-0;
    }

    .data-breach-event-dot {
        @apply w-2 h-2 rounded-full;
    }

    .data-breach-event-dot-click {
        @apply bg-red-500;
    }

    .data-breach-event-dot-hover {
        @apply bg-blue-500;
    }

    .data-breach-event-dot-move {
        @apply bg-gray-500;
    }

    .data-breach-event-dot-page-load {
        @apply bg-blue-500;
    }

    .data-breach-event-dot-error {
        @apply bg-yellow-500;
    }

    /* Form Styles */
    .data-breach-form-group {
        @apply space-y-4;
    }

    .data-breach-form-label {
        @apply block text-sm font-medium text-gray-700 mb-2;
    }

    .data-breach-form-input {
        @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500;
    }

    .data-breach-form-input-readonly {
        @apply bg-gray-50;
    }

    /* Loading States */
    .data-breach-loading {
        @apply opacity-50 pointer-events-none;
    }

    .data-breach-loading-spinner {
        @apply animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .data-breach-analytics-grid {
            @apply grid-cols-2;
        }
        
        .data-breach-card-header {
            @apply flex-col items-start space-y-4;
        }
    }

    @media (max-width: 640px) {
        .data-breach-analytics-grid {
            @apply grid-cols-1;
        }
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        .data-breach-card {
            @apply bg-gray-800 border-gray-700;
        }
        
        .data-breach-card-title {
            @apply text-gray-100;
        }
        
        .data-breach-form-label {
            @apply text-gray-300;
        }
        
        .data-breach-form-input {
            @apply bg-gray-700 border-gray-600 text-gray-100;
        }
        
        .data-breach-event-list {
            @apply bg-gray-700;
        }
        
        .data-breach-event-item {
            @apply border-gray-600;
        }
    }

    /* Accessibility Improvements */
    .data-breach-focus-visible {
        @apply focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2;
    }

    .data-breach-sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Print Styles */
    @media print {
        .data-breach-container {
            @apply text-black bg-white;
        }
        
        .data-breach-card {
            @apply shadow-none border border-gray-300;
        }
        
        .data-breach-btn {
            @apply hidden;
        }
    }
</style> 