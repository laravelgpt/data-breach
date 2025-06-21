<?php

namespace LaravelGPT\DataBreach;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use LaravelGPT\DataBreach\Console\InstallCommand;
use LaravelGPT\DataBreach\Console\PublishFrontendCommand;
use LaravelGPT\DataBreach\Services\PasswordBreachService;
use LaravelGPT\DataBreach\Services\IpReputationService;
use LaravelGPT\DataBreach\Services\MalwareScanService;
use LaravelGPT\DataBreach\Services\DarkWebMonitorService;
use LaravelGPT\DataBreach\Services\AlertDispatcherService;
use LaravelGPT\DataBreach\Services\PasskeyGeneratorService;
use LaravelGPT\DataBreach\Services\CursorAnalyticsService;
use LaravelGPT\DataBreach\Services\SessionReplayService;

class DataBreachServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/data-breach.php', 'data-breach');

        // Core security services
        $this->app->singleton(PasswordBreachService::class);
        $this->app->singleton(IpReputationService::class);
        $this->app->singleton(MalwareScanService::class);
        $this->app->singleton(DarkWebMonitorService::class);
        $this->app->singleton(AlertDispatcherService::class);
        $this->app->singleton(PasskeyGeneratorService::class);
        
        // New analytics services
        $this->app->singleton(CursorAnalyticsService::class);
        $this->app->singleton(SessionReplayService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'data-breach');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/data-breach.php' => config_path('data-breach.php'),
            ], 'data-breach-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/data-breach'),
            ], 'data-breach-views');

            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/data-breach'),
            ], 'data-breach-assets');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'data-breach-migrations');

            $this->commands([
                InstallCommand::class,
                PublishFrontendCommand::class,
            ]);
        }

        $this->registerBladeDirectives();
        $this->registerLivewireComponents();
        $this->registerVoltComponents();
        $this->registerRoutes();
    }

    /**
     * Register Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('dataBreachScripts', function () {
            return "<?php echo view('data-breach::partials.scripts')->render(); ?>";
        });

        Blade::directive('dataBreachStyles', function () {
            return "<?php echo view('data-breach::partials.styles')->render(); ?>";
        });

        Blade::directive('dataBreachCursorTracking', function () {
            return "<?php echo view('data-breach::partials.cursor-tracking')->render(); ?>";
        });
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            try {
                \Livewire\Livewire::component('data-breach::password-checker', \LaravelGPT\DataBreach\Livewire\PasswordChecker::class);
                \Livewire\Livewire::component('data-breach::ip-checker', \LaravelGPT\DataBreach\Livewire\IpChecker::class);
                \Livewire\Livewire::component('data-breach::malware-scanner', \LaravelGPT\DataBreach\Livewire\MalwareScanner::class);
                \Livewire\Livewire::component('data-breach::dark-web-monitor', \LaravelGPT\DataBreach\Livewire\DarkWebMonitor::class);
                \Livewire\Livewire::component('data-breach::cursor-analytics', \LaravelGPT\DataBreach\Livewire\CursorAnalytics::class);
                \Livewire\Livewire::component('data-breach::session-replay', \LaravelGPT\DataBreach\Livewire\SessionReplay::class);
            } catch (\Exception $e) {
                // Log error but don't break the application
                \Illuminate\Support\Facades\Log::warning('Failed to register Livewire components', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Register Volt components.
     */
    protected function registerVoltComponents(): void
    {
        if (class_exists(\Laravel\Volt\Volt::class)) {
            try {
                \Laravel\Volt\Volt::mount(__DIR__.'/../resources/views/volt');
            } catch (\Exception $e) {
                // Log error but don't break the application
                \Illuminate\Support\Facades\Log::warning('Failed to register Volt components', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Register routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../routes/web.php');
    }
} 