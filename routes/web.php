<?php

use Illuminate\Support\Facades\Route;
use Aws\AppConfigData\AppConfigDataClient;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {

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

        return $config_array['WELCOME_MESSAGE'] . "POPER_CUSTOM_VARIABLE -> " . $config_array['POPER_CUSTOM_VARIABLE'] . "\n Debug: " . print_r($config_array, true);

    } catch (\Exception $e) {
        //  Debug 可能错误，例如 AppConfig 未配置或无法访问
        return 'Error loading configuration: ' . $e->getMessage();
    }

    // $message = env('POPER_CUSTOM_VARIABLE', 'NULL'); // 获取环境变量，若不存在用 NULL 做默认值。
    // return "Hello from POPER TEST! POPER_CUSTOM_VARIABLE -> " . $message;
});
