<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Aws\AppConfigData\AppConfigDataClient;

Route::get('/', function () {
    // 缓存键名
    $cacheKey = 'appconfig_data';
    // 缓存过期时间（秒）
    $cacheDuration = 300; // 5分钟

    // 尝试从缓存中获取配置
    $config_array = Cache::get($cacheKey);
    
    if (!$config_array) {
        // 如果缓存中没有数据，则从 AWS AppConfig 获取
        $appConfigDataClient = new AppConfigDataClient([
            'region' => env('AWS_REGION', 'ap-northeast-1'),
            'version' => 'latest',
        ]);

        $applicationName = env('APPCONFIG_APPLICATION_NAME');
        $environmentName = env('APPCONFIG_ENVIRONMENT_NAME');
        $configurationProfileName = env('APPCONFIG_CONFIGURATION_PROFILE_NAME');
        $clientId = env('APPCONFIG_CLIENT_ID');

        try {
            $startSessionResult = $appConfigDataClient->startConfigurationSession([
                'ApplicationIdentifier' => $applicationName,
                'ConfigurationProfileIdentifier' => $configurationProfileName,
                'EnvironmentIdentifier' => $environmentName,
                'ClientId' => $clientId
            ]);

            $token = $startSessionResult['InitialConfigurationToken'];

            $configResult = $appConfigDataClient->getLatestConfiguration([
                'ConfigurationToken' => $token
            ]);
            
            $config_content = $configResult['Configuration'];
            $config_array = json_decode($config_content, true);

            // 将配置存入缓存
            Cache::put($cacheKey, $config_array, $cacheDuration);
        } catch (\Exception $e) {
            return 'Error loading configuration: ' . $e->getMessage();
        }
    }

    return $config_array['WELCOME_MESSAGE'] . "POPER_CUSTOM_VARIABLE -> " . 
           $config_array['POPER_CUSTOM_VARIABLE'] . "\n Debug: " . print_r($config_array, true);
});

Route::get('/error', function () {
    Log::error('ERR Test', [
        'trace_id' => request()->header('X-Amzn-Trace-Id', 'N/A'),
    ]);
    abort(500, 'This is a simulated error on the /error route.');
});