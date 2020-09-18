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
Route::get('/api/checklist/{checklist}', 'ChecklistController@show')->name('api.course.checklist.show');

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/', '/home', 301);

    Route::impersonate(); // https: //github.com/404labfr/laravel-impersonate

    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/course/{course}', 'CourseController@show')->name('course.show');
    Route::post('/course/{course}/paper', 'PaperController@store')->name('course.paper.store');
    Route::post('/course/{course}/comment', 'CommentController@store')->name('course.comment.store');

    Route::get('/course/{course}/checklist', 'ChecklistController@create')->name('course.checklist.create');
    Route::get('/checklist/{checklist}', 'ChecklistController@show')->name('course.checklist.show');
    Route::get('/checklist/{checklist}/pdf', 'ChecklistPdfController@show')->name('course.checklist.pdf');

    Route::get('/api/course/{course:code}/dropdown-options', 'Api\DropdownOptionsController@show')->name('api.course.paper_options');

    Route::get('/paper/{paper}', 'PaperController@show')->name('paper.show');
    Route::delete('/paper/{paper}', 'PaperController@destroy')->name('paper.delete');
    Route::get('/archivedpaper/{id}', 'ArchivedPaperController@show')->name('archived.paper.show');

    Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {
        Route::get('log', 'Admin\ActivityLogController@index')->name('activity.index');
        Route::get('course', 'Admin\CourseController@index')->name('course.index');
        Route::get('paper', 'Admin\PaperController@index')->name('paper.index');

        Route::get('user/{user}/export', 'Admin\GdprExportController@show')->name('gdpr.export.user');
        Route::post('user/{user}/anonmyise', 'Admin\GdprAnonymiseController@store')->name('gdpr.anonymise.user');

        Route::get('user', 'Admin\UserController@index')->name('user.index');
        Route::get('user/{user}', 'Admin\UserController@show')->name('user.show');
        Route::post('user', 'Admin\UserController@store')->name('user.store');
        Route::delete('user/{user}', 'Admin\UserController@destroy')->name('admin.user.delete');
        Route::post('user/{id}/undelete', 'Admin\UserController@reenable')->name('admin.user.undelete');

        Route::get('search/user', 'Admin\UserSearchController@show')->name('user.search');
        Route::post('course/{course}/users', 'Admin\CourseUsersController@update')->name('course.users.update');
        Route::post('courses/remove-staff', 'Admin\CourseUsersController@destroy')->name('admin.courses.clear_staff');

        Route::post('course/{course}/disable', 'Admin\CourseStatusController@disable')->name('course.disable');
        Route::post('course/{id}/enable', 'Admin\CourseStatusController@enable')->name('course.enable');

        Route::post('discipline/contacts', 'Admin\DisciplineContactController@update')->name('discipline.contacts.update');

        Route::post('wlm/import', 'Admin\WlmImportController@update')->name('wlm.import');
        Route::post('user/{user}/toggle-admin', 'Admin\AdminPermissionController@update')->name('admin.toggle');
        Route::get('options', 'Admin\OptionsController@edit')->name('admin.options.edit');
        Route::post('options', 'Admin\OptionsController@update')->name('admin.options.update');

        Route::get('archives', 'Admin\ArchiveController@index')->name('archive.index');
        Route::post('course/{course}/archive', 'Admin\ArchiveCourseController@store')->name('course.papers.archive');
        Route::get('area/archive', 'Admin\ArchiveAreaController@show')
                ->name('area.papers.archive_form');
        Route::post('area/archive', 'Admin\ArchiveAreaController@store')->name('area.papers.archive');

        Route::get('/notify/externals', 'Admin\NotifyExternalsController@show')
                ->name('admin.notify.externals.show');
        Route::post('notify/externals', 'Admin\NotifyExternalsController@store')->name('admin.notify.externals');
        Route::post('notify/{course}/externals', 'Admin\NotifyExternalsController@course')->name('admin.notify.externals_course');

        Route::post('/export/registry', 'Admin\ExportPapersForRegistryController@store')->name('export.paper.registry');
        Route::get('/download/registry/{user}', 'Admin\DownloadPapersForRegistryController@show')->name('download.papers.registry');

        Route::post('/export/checklists', 'Admin\ExportChecklistsController@store')->name('checklist.bulk_download');
        Route::get('/download/checklists/{user}', 'Admin\DownloadChecklistsController@show')->name('download.checklists');
    });
});
