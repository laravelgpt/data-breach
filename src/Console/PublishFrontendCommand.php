<?php

namespace LaravelGPT\DataBreach\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishFrontendCommand extends Command
{
    protected $signature = 'data-breach:publish-frontend {frontend?}';
    protected $description = 'Publish frontend assets for LaravelGPT Data Breach package';

    public function handle()
    {
        $frontend = $this->argument('frontend') ?: $this->choice(
            'Choose frontend to publish:',
            [
                'livewire' => 'Livewire 3 Components',
                'volt' => 'Volt Components',
                'vue' => 'Vue.js Components',
                'react' => 'React Components',
                'blade' => 'Blade Views',
                'all' => 'All Frontend Assets'
            ],
            'all'
        );

        $this->info('🚀 Publishing LaravelGPT Data Breach Frontend Assets');
        $this->info('==================================================');

        if ($frontend === 'all') {
            $this->publishAll();
        } else {
            $this->publishFrontend($frontend);
        }

        $this->info('');
        $this->info('✅ Frontend assets published successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Customize the published components as needed');
        $this->info('2. Include the components in your views');
        $this->info('3. Configure any additional styling or functionality');
    }

    protected function publishAll(): void
    {
        $frontends = ['livewire', 'volt', 'vue', 'react', 'blade'];
        
        foreach ($frontends as $frontend) {
            $this->publishFrontend($frontend);
        }
    }

    protected function publishFrontend(string $frontend): void
    {
        $this->info("Publishing {$frontend} assets...");

        switch ($frontend) {
            case 'livewire':
                $this->publishLivewire();
                break;
            case 'volt':
                $this->publishVolt();
                break;
            case 'vue':
                $this->publishVue();
                break;
            case 'react':
                $this->publishReact();
                break;
            case 'blade':
                $this->publishBlade();
                break;
        }
    }

    protected function publishLivewire(): void
    {
        $sourceDir = __DIR__ . '/../../resources/views/livewire';
        $targetDir = resource_path('views/vendor/data-breach/livewire');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($sourceDir)) {
            File::copyDirectory($sourceDir, $targetDir);
            $this->info('✅ Livewire components published to: ' . $targetDir);
        } else {
            $this->warn('⚠️ Livewire components not found in source');
        }
    }

    protected function publishVolt(): void
    {
        $sourceDir = __DIR__ . '/../../resources/views/volt';
        $targetDir = resource_path('views/vendor/data-breach/volt');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($sourceDir)) {
            File::copyDirectory($sourceDir, $targetDir);
            $this->info('✅ Volt components published to: ' . $targetDir);
        } else {
            $this->warn('⚠️ Volt components not found in source');
        }
    }

    protected function publishVue(): void
    {
        $sourceDir = __DIR__ . '/../../resources/js/components';
        $targetDir = resource_path('js/components/data-breach');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($sourceDir)) {
            $vueFiles = File::glob($sourceDir . '/*.vue');
            foreach ($vueFiles as $file) {
                $filename = basename($file);
                File::copy($file, $targetDir . '/' . $filename);
            }
            $this->info('✅ Vue components published to: ' . $targetDir);
        } else {
            $this->warn('⚠️ Vue components not found in source');
        }
    }

    protected function publishReact(): void
    {
        $sourceDir = __DIR__ . '/../../resources/js/components';
        $targetDir = resource_path('js/components/data-breach');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($sourceDir)) {
            $jsxFiles = File::glob($sourceDir . '/*.jsx');
            foreach ($jsxFiles as $file) {
                $filename = basename($file);
                File::copy($file, $targetDir . '/' . $filename);
            }
            $this->info('✅ React components published to: ' . $targetDir);
        } else {
            $this->warn('⚠️ React components not found in source');
        }
    }

    protected function publishBlade(): void
    {
        $sourceDir = __DIR__ . '/../../resources/views/blade';
        $targetDir = resource_path('views/vendor/data-breach/blade');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($sourceDir)) {
            File::copyDirectory($sourceDir, $targetDir);
            $this->info('✅ Blade views published to: ' . $targetDir);
        } else {
            $this->warn('⚠️ Blade views not found in source');
        }
    }
} 