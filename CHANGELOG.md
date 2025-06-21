# Changelog

All notable changes to the LaravelGPT Data Breach package will be documented in this file.

## [2.0.0] - 2024-01-01

### Added
- **Complete Auto-Updating Support**: Fixed package auto-discovery and auto-updating functionality
- **Missing Livewire Components**: Added IpChecker, MalwareScanner, and DarkWebMonitor components
- **Database Migrations**: Added comprehensive database schema for all security features
- **Web Routes**: Added proper web routing for all security tools
- **Dashboard View**: Created main dashboard with navigation and tool cards
- **Enhanced Service Provider**: Improved auto-discovery and asset publishing
- **Updated Dependencies**: Added proper Laravel 12 dependencies and removed unnecessary ones
- **Composer Scripts**: Added testing, analysis, and formatting scripts
- **Better Error Handling**: Improved error handling in Livewire components
- **Rate Limiting**: Added proper rate limiting for all API endpoints
- **Cache Support**: Added caching configuration for API responses
- **Logging Integration**: Enhanced logging for security events

### Fixed
- **Auto-Discovery**: Fixed Laravel auto-discovery configuration
- **Missing Components**: Added all referenced Livewire components
- **View Files**: Created missing Blade view files for all components
- **Dependencies**: Fixed composer.json dependencies and removed conflicts
- **Service Registration**: Improved service registration in service provider
- **Asset Publishing**: Added proper asset publishing configuration
- **Route Registration**: Fixed route loading and registration
- **Migration Loading**: Added proper migration loading from package

### Changed
- **Version Bump**: Updated to version 2.0.0 for major improvements
- **Package Structure**: Improved overall package structure and organization
- **Configuration**: Enhanced configuration file with better defaults
- **Documentation**: Updated README with comprehensive usage examples
- **Installation Process**: Improved installation command with better error handling

### Security
- **Rate Limiting**: Implemented proper rate limiting for all security endpoints
- **Input Validation**: Enhanced input validation for all user inputs
- **Error Handling**: Improved error handling to prevent information disclosure
- **API Security**: Added proper API authentication and authorization

## [1.0.0] - 2023-12-01

### Added
- Initial release of LaravelGPT Data Breach package
- Password breach checking with multiple API integrations
- IP reputation checking
- Malware scanning for files and URLs
- Dark web monitoring
- Cursor analytics and session replay
- Multiple frontend support (Livewire, Volt, Vue, React, Blade)
- Comprehensive configuration system
- Alert system with multiple notification channels 