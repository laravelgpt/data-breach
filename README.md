# LaravelGPT Data Breach Monitor

A comprehensive cybersecurity toolkit for Laravel 12 with multiple frontend integrations (Livewire 3, Volt, Vue.js, React, Blade) and advanced analytics capabilities.

## 🚀 Features

### 🔐 Core Security Features
- **Password Breach Check**: Integration with HIBP, DeHashed, LeakCheck APIs
- **Password Strength Analyzer**: Real-time password strength assessment
- **Malware Pattern Checker**: Advanced malware detection patterns
- **Secure Passkey Generator**: Generate cryptographically secure passkeys

### 🧠 Advanced Security Features
- **VirusTotal Integration**: Full file and URL scanning capabilities
- **Suspicious IP Checker**: Integration with AbuseIPDB, IPQS, VirusTotal
- **Geo-IP Alert System**: Location-based security alerts
- **Dark Web Monitor**: Monitor credentials on dark web platforms
- **Real-time Alert Dispatcher**: Email, Telegram, and log notifications
- **2FA Setup Recommendations**: Authenticator and YubiKey support

### 🎯 New Analytics & UX Features (v2.0)
- **Cursor Analytics**: Real-time cursor movement tracking and analysis
- **Session Replay**: Complete user session recording and playback
- **AI UX Feedback**: Automated analysis of user interaction patterns
- **Bug Report Enrichment**: Enhanced bug reports with cursor/session data
- **Multi-device Sync**: Cursor events synchronized across devices
- **Screen Recording**: Optional screenshot capture during sessions
- **Performance Analytics**: Velocity tracking and interaction speed analysis

### 🎨 Frontend Integration Options
- **Livewire 3** (Default): Real-time reactive components with modern syntax
- **Volt**: Laravel 12's new component system
- **Vue.js**: Modern reactive framework integration
- **React**: Component-based UI with Inertia.js
- **Blade**: Pure PHP/HTML views

## 📦 Installation

```bash
composer require laravelgpt/data-breach
```

### Interactive Installation

The package includes an interactive installer that lets you choose your preferred frontend:

```bash
php artisan data-breach:install
```

You'll be prompted to choose your frontend stack:
```
Choose your frontend stack:
[1] Livewire 3 (Default)
[2] Volt
[3] Vue.js
[4] React
[5] Blade Only
```

## ⚙️ Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="data-breach-config"
```

Configure your API keys in `config/data-breach.php`:

```php
return [
    'apis' => [
        'hibp' => env('HIBP_API_KEY'),
        'dehashed' => env('DEHASHED_API_KEY'),
        'virustotal' => env('VIRUSTOTAL_API_KEY'),
        'abuseipdb' => env('ABUSEIPDB_API_KEY'),
        'ipqs' => env('IPQS_API_KEY'),
    ],
    'frontend' => env('DATA_BREACH_FRONTEND', 'livewire'),
    'cursor' => [
        'tracking_enabled' => env('DATA_BREACH_CURSOR_TRACKING', true),
        'session_logging' => env('DATA_BREACH_CURSOR_SESSION_LOGGING', true),
        'analytics_enabled' => env('DATA_BREACH_CURSOR_ANALYTICS', true),
        'ai_ux_feedback' => env('DATA_BREACH_CURSOR_AI_UX_FEEDBACK', true),
        'bug_report_enrichment' => env('DATA_BREACH_CURSOR_BUG_REPORT', true),
        'archive_policy_days' => env('DATA_BREACH_CURSOR_ARCHIVE_DAYS', 30),
    ],
    'alerts' => [
        'email' => env('DATA_BREACH_EMAIL_ALERTS', true),
        'telegram' => env('DATA_BREACH_TELEGRAM_ALERTS', false),
        'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'telegram_chat_id' => env('TELEGRAM_CHAT_ID'),
    ],
];
```

## 🎯 Usage

### Basic Password Check

```php
use LaravelGPT\DataBreach\Services\PasswordBreachService;

$breachService = app(PasswordBreachService::class);
$result = $breachService->checkPassword('password123');
```

### IP Reputation Check

```php
use LaravelGPT\DataBreach\Services\IpReputationService;

$ipService = app(IpReputationService::class);
$result = $ipService->checkIp('8.8.8.8');
```

### Cursor Analytics

```php
use LaravelGPT\DataBreach\Services\CursorAnalyticsService;

$analyticsService = app(CursorAnalyticsService::class);

// Track cursor event
$analyticsService->trackCursorEvent($request, [
    'x' => 100,
    'y' => 200,
    'event_type' => 'click',
    'element_id' => 'submit-button',
    'velocity' => 150
]);

// Get session analytics
$analytics = $analyticsService->getSessionAnalytics($sessionKey);
```

### Session Replay

```php
use LaravelGPT\DataBreach\Services\SessionReplayService;

$replayService = app(SessionReplayService::class);

// Start recording
$sessionKey = $replayService->startRecording($request);

// Record events
$replayService->recordEvent($sessionKey, [
    'type' => 'click',
    'data' => ['x' => 100, 'y' => 200]
]);

// Stop and get data
$sessionData = $replayService->stopRecording($sessionKey);
```

## 🎨 Frontend Components

### Livewire 3 Component

```php
use LaravelGPT\DataBreach\Livewire\PasswordChecker;

// In your Blade view
<livewire:data-breach::password-checker />

// New analytics components
<livewire:data-breach::cursor-analytics />
<livewire:data-breach::session-replay />
```

### Volt Component

```php
// In your Volt component
use function Livewire\Volt\{state, mount};

state(['password' => '', 'result' => null]);

mount(function () {
    // Component initialization
});

$checkPassword = function () {
    $service = app(PasswordBreachService::class);
    $this->result = $service->checkPassword($this->password);
};
```

### Vue.js Component

```vue
<template>
    <password-checker />
    <cursor-analytics />
    <session-replay />
</template>

<script>
import PasswordChecker from '@/components/PasswordChecker.vue'
import CursorAnalytics from '@/components/CursorAnalytics.vue'
import SessionReplay from '@/components/SessionReplay.vue'

export default {
    components: {
        PasswordChecker,
        CursorAnalytics,
        SessionReplay
    }
}
</script>
```

### React Component

```jsx
import PasswordChecker from '@/components/PasswordChecker'
import CursorAnalytics from '@/components/CursorAnalytics'
import SessionReplay from '@/components/SessionReplay'

function App() {
    return (
        <div>
            <PasswordChecker />
            <CursorAnalytics />
            <SessionReplay />
        </div>
    )
}
```

## 🔧 API Endpoints

The package provides comprehensive RESTful API endpoints:

### Security Endpoints
- `POST /api/data-breach/password/check` - Check password breach
- `POST /api/data-breach/ip/check` - Check IP reputation
- `POST /api/data-breach/file/scan` - Scan file for malware
- `GET /api/data-breach/dark-web/search` - Search dark web
- `POST /api/data-breach/generate/passkey` - Generate secure passkey

### Analytics Endpoints
- `POST /api/data-breach/cursor/track` - Track cursor event
- `GET /api/data-breach/cursor/analytics/{sessionKey}` - Get cursor analytics
- `GET /api/data-breach/cursor/events/{sessionKey}` - Get cursor events
- `POST /api/data-breach/cursor/archive` - Archive old cursor data

### Session Replay Endpoints
- `POST /api/data-breach/session/start` - Start session recording
- `POST /api/data-breach/session/record` - Record session event
- `POST /api/data-breach/session/stop` - Stop session recording
- `GET /api/data-breach/session/replay/{sessionKey}` - Get session replay data
- `GET /api/data-breach/session/analytics/{sessionKey}` - Get session analytics

### Bug Report Endpoints
- `POST /api/data-breach/bug-report/enrich` - Enrich bug report with cursor data

## 🔍 Cursor Analytics Features

### Real-time Tracking
- Mouse movement tracking with velocity calculation
- Click and hover event capture
- Element interaction analysis
- Device type detection (desktop/mobile/tablet)

### Analytics Dashboard
- Total moves, clicks, and interactions
- Fast vs slow movement analysis
- Most hovered elements
- Session duration and event breakdown

### AI UX Feedback
- Automated interaction pattern analysis
- Performance bottleneck detection
- User experience optimization suggestions
- Heat map generation capabilities

## 📊 Session Replay Features

### Complete Session Recording
- Page load events with metadata
- Click, scroll, and form interaction tracking
- Error capture and reporting
- Screenshot capture (optional)

### Session Analytics
- Session duration and event count
- Device and browser information
- Interaction breakdown by type
- Performance metrics

### Multi-device Support
- Cross-device session synchronization
- User and tenant-based session organization
- Automatic cleanup and archiving

## 🛡️ Security & Privacy

### Data Protection
- All cursor and session data is encrypted at rest
- Automatic data archiving and cleanup
- GDPR-compliant data handling
- Configurable retention policies

### Rate Limiting
- Comprehensive rate limiting on all endpoints
- Configurable limits per endpoint type
- Abuse prevention and monitoring

### Authentication
- Laravel Sanctum integration for API protection
- Role-based access control
- Multi-tenant support
- Session-based security

## 🧪 Testing

```bash
composer test
```

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📞 Support

- Documentation: [https://docs.laravelgpt.com/data-breach](https://docs.laravelgpt.com/data-breach)
- Issues: [https://github.com/laravelgpt/data-breach/issues](https://github.com/laravelgpt/data-breach/issues)
- Email: support@laravelgpt.com

## 🔄 Changelog

### v2.0.0 - Major Update
- ✨ Added cursor analytics and tracking
- ✨ Added session replay functionality
- ✨ Added AI UX feedback system
- ✨ Added bug report enrichment
- ✨ Upgraded to Laravel 12 support
- ✨ Added Volt component support
- ✨ Enhanced Livewire 3 integration
- 🔧 Improved performance and security
- 🐛 Fixed various bugs and issues 