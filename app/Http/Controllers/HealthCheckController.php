<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;

class HealthCheckController extends Controller
{
    public function check()
    {
        try {
            $this->validateConfig();
            
            // 基础响应信息
            $response = [
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String()
            ];

            // 如果开启了debug，添加详细信息
            if (config('app.debug')) {
                $configPath = env('APP_ENV') === 'local' 
                    ? base_path('deploy/config.json')
                    : sprintf(
                        '%s/applications/%s/%s/%s',
                        env('AWS_APPCONFIG_PATH', '/opt/aws/appconfig'),
                        env('APP_NAME', 'laravel'),
                        env('APP_ENV', 'production'),
                        env('AWS_APPCONFIG_PROFILE', 'config.json')
                    );

                $response += [
                    'config_version' => file_exists($configPath) ? filemtime($configPath) : null,
                    'config_path' => $configPath,
                    'config_values' => [
                        'app.debug' => config('app.debug'),
                        'raw_config' => file_exists($configPath) ? file_get_contents($configPath) : null
                    ]
                ];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status' => 'unhealthy',
                'timestamp' => now()->toIso8601String()
            ];

            // 如果开启了debug，添加错误详情
            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 500);
        }
    }

    private function validateConfig()
    {
        $requiredConfigs = [
            'app.debug',
            'WELCOME_MESSAGE',
            'POPER_CUSTOM_VARIABLE'
        ];

        foreach ($requiredConfigs as $config) {
            if (!Config::has($config)) {
                throw new \Exception("Missing required configuration: {$config}");
            }
        }
    }
}