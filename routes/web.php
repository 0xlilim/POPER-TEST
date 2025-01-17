<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    $message = env('POPER_CUSTOM_VARIABLE', 'NULL'); // 获取环境变量，若不存在用 NULL 做默认值。
    return "Hello from POPER TEST! POPER_CUSTOM_VARIABLE -> " . $message;
});