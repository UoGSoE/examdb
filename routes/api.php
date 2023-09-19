<?php

Route::middleware('api.token')->group(function () {
    Route::get('/courses', [\App\Http\Controllers\Api\CourseController::class, 'index'])->name('api.course.index');
    Route::get('/course/{code}', [\App\Http\Controllers\Api\CourseController::class, 'show'])->name('api.course.show');
    Route::get('/course/{code}/staff', [\App\Http\Controllers\Api\CourseStaffController::class, 'show'])->name('api.course.staff');
    Route::get('/course/{code}/papers', [\App\Http\Controllers\Api\CoursePaperController::class, 'show'])->name('api.course.papers');
});
