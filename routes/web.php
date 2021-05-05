<?php


Route::get('/', [\App\Http\Controllers\BaseTenantHomepageController::class, 'show']);
Route::get('/login', [\App\Http\Controllers\Sysadmin\LoginController::class, 'show']);
Route::post('/login', [\App\Http\Controllers\Sysadmin\LoginController::class, 'login']);

Route::group(['middleware' => [
        'auth:sysadmin',
        \App\Http\Middleware\SysadminOnly::class
    ]
], function () {
    Route::get('/dashboard', [\App\Http\Controllers\Sysadmin\DashboardController::class, 'show']);
    Route::post('/logout', [\App\Http\Controllers\Sysadmin\LoginController::class, 'logout']);
});
