<?php

Route::get('/', [\App\Http\Controllers\Sysadmin\LoginController::class, 'show'])->name('sysadmin.login.show');
Route::post('/login', [\App\Http\Controllers\Sysadmin\LoginController::class, 'login'])->name('sysadmin.login');

Route::group(['middleware' => [
        'auth:sysadmin',
        \App\Http\Middleware\SysadminOnly::class
    ]
], function () {
    Route::get('/dashboard', [\App\Http\Controllers\Sysadmin\DashboardController::class, 'show'])->name('sysadmin.dashboard');
});
