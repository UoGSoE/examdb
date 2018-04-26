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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/course/{course}', 'CourseController@show')->name('course.show');
