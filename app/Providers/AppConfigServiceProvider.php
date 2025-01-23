<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AppConfigServiceProvider extends ServiceProvider
{
    protected string $configPath;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configPath = env('APP_ENV') === 'local' 
            ? base_path('deploy/config.json')
            : sprintf(
                '%s/%s/%s/%s',
                env('AWS_APPCONFIG_PATH', '/opt/aws/appconfig'),
                env('APP_NAME', 'error_appname'),
                env('APP_ENV', 'production'),
                env('AWS_APPCONFIG_PROFILE', 'config.json')
            );

        $this->loadAppConfig();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    protected function loadAppConfig(): void
    {
        if (!file_exists($this->configPath)) {
            Log::warning('Config file not found', ['path' => $this->configPath]);
            return;
        }

        try {
            $content = file_get_contents($this->configPath);
            $config = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            foreach ($config as $key => $value) {
                Config::set($key, $value);
            }

            Log::info('Configuration loaded successfully', [
                'path' => $this->configPath,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load configuration', [
                'error' => $e->getMessage(),
                'path' => $this->configPath
            ]);
        }
    }
}