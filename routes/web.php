<?php

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\UserController;

Auth::routes();
Route::post('/external-login', [\App\Http\Controllers\Auth\ExternalLoginController::class, 'sendLoginEmail'])->name('external-generate-login');
Route::get('/external-login/{user}', [\App\Http\Controllers\Auth\ExternalLoginController::class, 'login'])->name('external-login')->middleware('signed');
Route::get('/api/checklist/{checklist}', [\App\Http\Controllers\ChecklistController::class, 'show'])->name('api.course.checklist.show');
