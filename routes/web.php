<?php

Route::get('/', [\App\Http\Controllers\GlobalHomeController::class, 'show'])->name('home');
Route::get('/home', [\App\Http\Controllers\GlobalHomeController::class, 'show'])->name('redirected.home');
