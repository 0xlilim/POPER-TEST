<?php

namespace App\Http\Controllers;

class WelcomeController extends Controller
{
    public function index()
    {
        try {
            // 基础welcome 响应
            $response = [
                'welcome_message' => config('WELCOME_MESSAGE', 'Welcome'),
                'custom_variable' => config('POPER_CUSTOM_VARIABLE', 'default value'),
                'timestamp' => now()->toIso8601String()
            ];

            if (config('app.debug')) {
                $configPath = env('APP_ENV') === 'local' 
                    ? base_path('deploy/config.json')
                    : sprintf(
                        '%s/applications/%s/%s/%s',
                        env('AWS_APPCONFIG_PATH'),
                        env('APP_NAME'),
                        env('APP_ENV'),
                        env('AWS_APPCONFIG_PROFILE')
                    );

                $response += [
                    'debug_info' => [
                        'config_path' => $configPath,
                        'config_exists' => file_exists($configPath),
                        'config_version' => file_exists($configPath) ? filemtime($configPath) : null,
                        'raw_config' => file_exists($configPath) ? file_get_contents($configPath) : null,
                        'app_env' => env('APP_ENV'),
                        'all_config_values' => [
                            'WELCOME_MESSAGE' => config('WELCOME_MESSAGE'),
                            'POPER_CUSTOM_VARIABLE' => config('POPER_CUSTOM_VARIABLE'),
                            'app.debug' => config('app.debug')
                        ]
                    ]
                ];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'error' => 'Internal Server Error',
                'timestamp' => now()->toIso8601String()
            ];

            if (config('app.debug')) {
                $response['debug_info'] = [
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString()
                ];
            }

            return response()->json($response, 500);
        }
    }
}