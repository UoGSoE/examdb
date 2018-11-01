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
    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/course/{course}', 'CourseController@show')->name('course.show');
    Route::post('/course/{course}/paper', 'PaperController@store')->name('course.paper.store');
    Route::post('/course/{course}/solution', 'SolutionController@store')->name('course.solution.store');
    Route::post('/paper/{paper}/comment', 'PaperCommentController@store')->name('paper.comment');
    Route::post('/solution/{solution}/comment', 'SolutionCommentController@store')->name('solution.comment');
    Route::post('/paper/{paper}/approve', 'PaperApprovalController@store')->name('paper.approve');
    Route::post('/paper/{paper}/unapprove', 'PaperApprovalController@destroy')->name('paper.unapprove');

    Route::get('/paper/{paper}', 'PaperController@show')->name('paper.show');
});
