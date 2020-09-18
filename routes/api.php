<?php

use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CoursePaperController;
use App\Http\Controllers\Api\CourseStaffController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api.token'], function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('api.course.index');
    Route::get('/course/{code}', [CourseController::class, 'show'])->name('api.course.show');
    Route::get('/course/{code}/staff', [CourseStaffController::class, 'show'])->name('api.course.staff');
    Route::get('/course/{code}/papers', [CoursePaperController::class, 'show'])->name('api.course.papers');
});
