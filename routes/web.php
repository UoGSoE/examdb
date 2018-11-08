<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */


Auth::routes();
Route::post('/external-login', 'Auth\ExternalLoginController@sendLoginEmail')->name('external-generate-login');
Route::get('/external-login/{user}', 'Auth\ExternalLoginController@login')->name('external-login')->middleware('signed');

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/', '/home', 301);
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/course/{course}', 'CourseController@show')->name('course.show');
    Route::post('/course/{course}/paper', 'PaperController@store')->name('course.paper.store');
    Route::post('/paper/{paper}/comment', 'PaperCommentController@store')->name('paper.comment');
    Route::post('/course/{course}/approve/{category}', 'PaperApprovalController@store')->name('paper.approve');
    Route::post('/course/{course}/unapprove/{category}', 'PaperApprovalController@destroy')->name('paper.unapprove');

    Route::get('/paper/{paper}', 'PaperController@show')->name('paper.show');
    Route::delete('/paper/{paper}', 'PaperController@destroy')->name('paper.delete');

    Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {
        Route::get('log', 'Admin\ActivityLogController@index')->name('activity.index');
        Route::get('course', 'Admin\CourseController@index')->name('course.index');
        Route::get('paper', 'Admin\PaperController@index')->name('paper.index');
        Route::get('user', 'Admin\UserController@index')->name('user.index');
        Route::post('user', 'Admin\UserController@store')->name('user.store');
    });
});
