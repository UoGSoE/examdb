<?php

use Illuminate\Http\Request;

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
    Route::get('/courses', 'Api\CourseController@index')->name('api.course.index');
    Route::get('/course/{code}', 'Api\CourseController@show')->name('api.course.show');
    Route::get('/course/{code}/staff', 'Api\CourseStaffController@show')->name('api.course.staff');
    Route::get('/course/{code}/papers', 'Api\CoursePaperController@show')->name('api.course.papers');
});
