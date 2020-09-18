<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ChecklistPdfController;
use App\Http\Controllers\Admin\ArchiveController;
use App\Http\Controllers\Admin\OptionsController;
use App\Http\Controllers\ArchivedPaperController;
use App\Http\Controllers\Admin\WlmImportController;
use App\Http\Controllers\Admin\GdprExportController;
use App\Http\Controllers\Admin\UserSearchController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ArchiveAreaController;
use App\Http\Controllers\Admin\CourseUsersController;
use App\Http\Controllers\Admin\CourseStatusController;
use App\Http\Controllers\Auth\ExternalLoginController;
use App\Http\Controllers\Admin\ArchiveCourseController;
use App\Http\Controllers\Admin\GdprAnonymiseController;
use App\Http\Controllers\Api\DropdownOptionsController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\NotifyExternalsController;
use App\Http\Controllers\Admin\ExportChecklistsController;
use App\Http\Controllers\Admin\DisciplineContactController;
use App\Http\Controllers\Admin\DownloadChecklistsController;
use App\Http\Controllers\Admin\ExportPapersForRegistryController;
use App\Http\Controllers\Admin\DownloadPapersForRegistryController;
use App\Http\Controllers\Admin\PaperController as AdminPaperController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;

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
Route::post('/external-login', [ExternalLoginController::class, 'sendLoginEmail'])->name('external-generate-login');
Route::get('/external-login/{user}', [ExternalLoginController::class, 'login'])->name('external-login')->middleware('signed');
Route::get('/api/checklist/{checklist}', [ChecklistController::class, 'show'])->name('api.course.checklist.show');

Route::group(['middleware' => 'auth'], function () {
    Route::redirect('/', '/home', 301);

    Route::impersonate(); // https: //github.com/404labfr/laravel-impersonate

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/course/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::post('/course/{course}/paper', [PaperController::class, 'store'])->name('course.paper.store');
    Route::post('/course/{course}/comment', [CommentController::class, 'store'])->name('course.comment.store');

    Route::get('/course/{course}/checklist', [ChecklistController::class, 'create'])->name('course.checklist.create');
    Route::get('/checklist/{checklist}', [ChecklistController::class, 'show'])->name('course.checklist.show');
    Route::get('/checklist/{checklist}/pdf', [ChecklistPdfController::class, 'show'])->name('course.checklist.pdf');

    Route::get('/api/course/{course:code}/dropdown-options', [DropdownOptionsController::class, 'show'])->name('api.course.paper_options');

    Route::get('/paper/{paper}', [PaperController::class, 'show'])->name('paper.show');
    Route::delete('/paper/{paper}', [PaperController::class, 'destroy'])->name('paper.delete');
    Route::get('/archivedpaper/{id}', [ArchivedPaperController::class, 'show'])->name('archived.paper.show');

    Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {
        Route::get('log', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('course', [AdminCourseController::class, 'index'])->name('course.index');
        Route::get('paper', [AdminPaperController::class, 'index'])->name('paper.index');

        Route::get('user/{user}/export', [GdprExportController::class, 'show'])->name('gdpr.export.user');
        Route::post('user/{user}/anonmyise', [GdprAnonymiseController::class, 'store'])->name('gdpr.anonymise.user');

        Route::get('user', [UserController::class, 'index'])->name('user.index');
        Route::get('user/{user}', [UserController::class, 'show'])->name('user.show');
        Route::post('user', [UserController::class, 'store'])->name('user.store');
        Route::delete('user/{user}', [UserController::class, 'destroy'])->name('admin.user.delete');
        Route::post('user/{id}/undelete', [UserController::class, 'reenable'])->name('admin.user.undelete');

        Route::get('search/user', [UserSearchController::class, 'show'])->name('user.search');
        Route::post('course/{course}/users', [CourseUsersController::class, 'update'])->name('course.users.update');
        Route::post('courses/remove-staff', [CourseUsersController::class, 'destroy'])->name('admin.courses.clear_staff');

        Route::post('course/{course}/disable', [CourseStatusController::class, 'disable'])->name('course.disable');
        Route::post('course/{id}/enable', [CourseStatusController::class, 'enable'])->name('course.enable');

        Route::post('discipline/contacts', [DisciplineContactController::class, 'update'])->name('discipline.contacts.update');

        Route::post('wlm/import', [WlmImportController::class, 'update'])->name('wlm.import');
        Route::post('user/{user}/toggle-admin', [AdminPermissionController::class, 'update'])->name('admin.toggle');
        Route::get('options', [OptionsController::class, 'edit'])->name('admin.options.edit');
        Route::post('options', [OptionsController::class, 'update'])->name('admin.options.update');

        Route::get('archives', [ArchiveController::class, 'index'])->name('archive.index');
        Route::post('course/{course}/archive', [ArchiveCourseController::class, 'store'])->name('course.papers.archive');
        Route::get('area/archive', [ArchiveAreaController::class, 'show'])
                ->name('area.papers.archive_form');
        Route::post('area/archive', [ArchiveAreaController::class, 'store'])->name('area.papers.archive');

        Route::get('/notify/externals', [NotifyExternalsController::class, 'show'])
                ->name('admin.notify.externals.show');
        Route::post('notify/externals', [NotifyExternalsController::class, 'store'])->name('admin.notify.externals');
        Route::post('notify/{course}/externals', [NotifyExternalsController::class, 'course'])->name('admin.notify.externals_course');

        Route::post('/export/registry', [ExportPapersForRegistryController::class, 'store'])->name('export.paper.registry');
        Route::get('/download/registry/{user}', [DownloadPapersForRegistryController::class, 'show'])->name('download.papers.registry');

        Route::post('/export/checklists', [ExportChecklistsController::class, 'store'])->name('checklist.bulk_download');
        Route::get('/download/checklists/{user}', [DownloadChecklistsController::class, 'show'])->name('download.checklists');
    });
});
