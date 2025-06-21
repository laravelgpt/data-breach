<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API keys for various security services
    |
    */
    'apis' => [
        'hibp' => env('HIBP_API_KEY'),
        'dehashed' => env('DEHASHED_API_KEY'),
        'leakcheck' => env('LEAKCHECK_API_KEY'),
        'virustotal' => env('VIRUSTOTAL_API_KEY'),
        'abuseipdb' => env('ABUSEIPDB_API_KEY'),
        'ipqs' => env('IPQS_API_KEY'),
        'ghostproject' => env('GHOSTPROJECT_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Configuration
    |--------------------------------------------------------------------------
    |
    | Choose your preferred frontend integration
    | Options: livewire, vue, react, blade
    |
    */
    'frontend' => env('DATA_BREACH_FRONTEND', 'livewire'),

    /*
    |--------------------------------------------------------------------------
    | Alert Configuration
    |--------------------------------------------------------------------------
    |
    | Configure alert notifications
    |
    */
    'alerts' => [
        'email' => [
            'enabled' => env('DATA_BREACH_EMAIL_ALERTS', true),
            'recipients' => env('DATA_BREACH_EMAIL_RECIPIENTS', []),
        ],
        'telegram' => [
            'enabled' => env('DATA_BREACH_TELEGRAM_ALERTS', false),
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('TELEGRAM_CHAT_ID'),
        ],
        'slack' => [
            'enabled' => env('DATA_BREACH_SLACK_ALERTS', false),
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Configuration
    |--------------------------------------------------------------------------
    |
    | Password strength and generation settings
    |
    */
    'password' => [
        'min_length' => env('DATA_BREACH_MIN_PASSWORD_LENGTH', 12),
        'require_uppercase' => env('DATA_BREACH_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('DATA_BREACH_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('DATA_BREACH_REQUIRE_NUMBERS', true),
        'require_symbols' => env('DATA_BREACH_REQUIRE_SYMBOLS', true),
        'exclude_similar' => env('DATA_BREACH_EXCLUDE_SIMILAR', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Reputation Configuration
    |--------------------------------------------------------------------------
    |
    | IP checking and reputation settings
    |
    */
    'ip_reputation' => [
        'check_proxy' => env('DATA_BREACH_CHECK_PROXY', true),
        'check_vpn' => env('DATA_BREACH_CHECK_VPN', true),
        'check_tor' => env('DATA_BREACH_CHECK_TOR', true),
        'check_botnet' => env('DATA_BREACH_CHECK_BOTNET', true),
        'geo_restrictions' => [
            'enabled' => env('DATA_BREACH_GEO_RESTRICTIONS', false),
            'allowed_countries' => env('DATA_BREACH_ALLOWED_COUNTRIES', ['US', 'CA', 'GB']),
            'blocked_countries' => env('DATA_BREACH_BLOCKED_COUNTRIES', []),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Malware Scan Configuration
    |--------------------------------------------------------------------------
    |
    | File and URL scanning settings
    |
    */
    'malware_scan' => [
        'max_file_size' => env('DATA_BREACH_MAX_FILE_SIZE', 32 * 1024 * 1024), // 32MB
        'allowed_extensions' => [
            'exe', 'dll', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js',
            'jar', 'msi', 'msm', 'msp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'pdf', 'zip', 'rar', '7z', 'tar', 'gz', 'bz2'
        ],
        'scan_timeout' => env('DATA_BREACH_SCAN_TIMEOUT', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Dark Web Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | Dark web monitoring settings
    |
    */
    'dark_web' => [
        'monitor_email' => env('DATA_BREACH_MONITOR_EMAIL', true),
        'monitor_domain' => env('DATA_BREACH_MONITOR_DOMAIN', true),
        'scan_interval' => env('DATA_BREACH_SCAN_INTERVAL', 24), // hours
        'max_results' => env('DATA_BREACH_MAX_DARK_WEB_RESULTS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API rate limiting configuration
    |
    */
    'rate_limiting' => [
        'password_check' => env('DATA_BREACH_PASSWORD_RATE_LIMIT', 60), // per minute
        'ip_check' => env('DATA_BREACH_IP_RATE_LIMIT', 120), // per minute
        'file_scan' => env('DATA_BREACH_FILE_SCAN_RATE_LIMIT', 10), // per minute
        'dark_web_search' => env('DATA_BREACH_DARK_WEB_RATE_LIMIT', 30), // per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for API responses
    |
    */
    'cache' => [
        'enabled' => env('DATA_BREACH_CACHE_ENABLED', true),
        'ttl' => [
            'password_check' => env('DATA_BREACH_PASSWORD_CACHE_TTL', 3600), // 1 hour
            'ip_check' => env('DATA_BREACH_IP_CACHE_TTL', 1800), // 30 minutes
            'file_scan' => env('DATA_BREACH_FILE_SCAN_CACHE_TTL', 7200), // 2 hours
            'dark_web' => env('DATA_BREACH_DARK_WEB_CACHE_TTL', 86400), // 24 hours
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Logging settings for security events
    |
    */
    'logging' => [
        'enabled' => env('DATA_BREACH_LOGGING_ENABLED', true),
        'channel' => env('DATA_BREACH_LOG_CHANNEL', 'stack'),
        'level' => env('DATA_BREACH_LOG_LEVEL', 'info'),
        'log_breaches' => env('DATA_BREACH_LOG_BREACHES', true),
        'log_suspicious_ips' => env('DATA_BREACH_LOG_SUSPICIOUS_IPS', true),
        'log_malware_detections' => env('DATA_BREACH_LOG_MALWARE_DETECTIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cursor & Session Analytics
    |--------------------------------------------------------------------------
    |
    | Track cursor events, session replays, and analytics for UX feedback.
    |
    */
    'cursor' => [
        'tracking_enabled' => env('DATA_BREACH_CURSOR_TRACKING', true),
        'session_logging' => env('DATA_BREACH_CURSOR_SESSION_LOGGING', true),
        'analytics_enabled' => env('DATA_BREACH_CURSOR_ANALYTICS', true),
        'ai_ux_feedback' => env('DATA_BREACH_CURSOR_AI_UX_FEEDBACK', true),
        'bug_report_enrichment' => env('DATA_BREACH_CURSOR_BUG_REPORT', true),
        'archive_policy_days' => env('DATA_BREACH_CURSOR_ARCHIVE_DAYS', 30),
    ],
]; 