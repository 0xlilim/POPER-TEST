<?php

use Illuminate\Support\Facades\Route;
use Aws\AppConfig\AppConfigClient;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    $appConfigClient = new AppConfigClient([
        'region' => env('AWS_REGION', 'ap-northeast-1'), // 你的 AWS 区域
        'version' => 'latest',
    ]);

    $applicationName = env('APPCONFIG_APPLICATION_NAME');
    $environmentName = env('APPCONFIG_ENVIRONMENT_NAME');
    $configurationProfileName = env('APPCONFIG_CONFIGURATION_PROFILE_NAME');
    $clientId = env('APPCONFIG_CLIENT_ID');


    try {

        $result = $appConfigClient->getConfiguration([
            'Application' => $applicationName,
            'ConfigurationProfile' => $configurationProfileName,
            'Environment' => $environmentName,
            'ClientId' => $clientId,
        ]);

        $configuration = json_decode($result['Configuration']->getContents(), true);

        return $configuration['WELCOME_MESSAGE'] . "POPER_CUSTOM_VARIABLE -> " . $configuration['POPER_CUSTOM_VARIABLE'];
    } catch (\Exception $e) {
        //  Debug 可能错误，例如 AppConfig 未配置或无法访问
        return 'Error loading configuration: ' . $e->getMessage();
    }

    // $message = env('POPER_CUSTOM_VARIABLE', 'NULL'); // 获取环境变量，若不存在用 NULL 做默认值。
    // return "Hello from POPER TEST! POPER_CUSTOM_VARIABLE -> " . $message;
});
