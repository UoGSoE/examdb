<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Auth::routes();
    Route::get('/sysadmin/impersonate/{token}', [\App\Http\Controllers\Sysadmin\ImpersonationController::class, 'store'])->name('sysadmin.impersonate.start');
    Route::post('/external-login', [\App\Http\Controllers\Auth\ExternalLoginController::class, 'sendLoginEmail'])->name('external-generate-login');
    Route::get('/external-login/{user}', [\App\Http\Controllers\Auth\ExternalLoginController::class, 'login'])->name('external-login')->middleware('signed');
    Route::get('/api/checklist/{checklist}', [\App\Http\Controllers\ChecklistController::class, 'show'])->name('api.course.checklist.show');

    Route::group(['middleware' => 'auth'], function () {
        Route::redirect('/', '/home', 301);

        Route::impersonate(); // https: //github.com/404labfr/laravel-impersonate

        Route::get('/home', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::get('/course/{course}', [\App\Http\Controllers\CourseController::class, 'show'])->name('course.show');
        Route::post('/course/{course}/paper', [\App\Http\Controllers\PaperController::class, 'store'])->name('course.paper.store');
        Route::post('/course/{course}/comment', [\App\Http\Controllers\CommentController::class, 'store'])->name('course.comment.store');
        Route::post('/course/{course}/approve-registry', [\App\Http\Controllers\PaperForRegistryApprovalController::class, 'approve'])->name('registry.approve');

        Route::get('/course/{course}/checklist', [\App\Http\Controllers\ChecklistController::class, 'create'])->name('course.checklist.create');
        Route::get('/checklist/{checklist}', [\App\Http\Controllers\ChecklistController::class, 'show'])->name('course.checklist.show');
        Route::get('/checklist/{checklist}/pdf', [\App\Http\Controllers\ChecklistPdfController::class, 'show'])->name('course.checklist.pdf');

        Route::get('/api/course/{course:code}/dropdown-options', [\App\Http\Controllers\Api\DropdownOptionsController::class, 'show'])->name('api.course.paper_options');

        Route::get('/paper/{paper}', [\App\Http\Controllers\PaperController::class, 'show'])->name('paper.show');
        Route::delete('/paper/{paper}', [\App\Http\Controllers\PaperController::class, 'destroy'])->name('paper.delete');
        Route::get('/archivedpaper/{id}', [\App\Http\Controllers\ArchivedPaperController::class, 'show'])->name('archived.paper.show');

        Route::group(['middleware' => 'admin', 'prefix' => '/admin'], function () {
            Route::get('log', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity.index');
            Route::get('course', [\App\Http\Controllers\Admin\CourseController::class, 'index'])->name('course.index');
            Route::get('paper', [\App\Http\Controllers\Admin\PaperController::class, 'index'])->name('paper.index');

            Route::get('export/courses', [\App\Http\Controllers\Admin\CourseExportController::class, 'show'])->name('admin.course.export');
            Route::get('export/papers', [\App\Http\Controllers\Admin\PaperExportController::class, 'show'])->name('admin.paper.export');

            Route::get('user/{user}/export', [\App\Http\Controllers\Admin\GdprExportController::class, 'show'])->name('gdpr.export.user');
            Route::post('user/{user}/anonmyise', [\App\Http\Controllers\Admin\GdprAnonymiseController::class, 'store'])->name('gdpr.anonymise.user');

            Route::get('user', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('user.index');
            Route::get('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('user.show');
            Route::post('user', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('user.store');
            Route::delete('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.user.delete');
            Route::post('user/{id}/undelete', [\App\Http\Controllers\Admin\UserController::class, 'reenable'])->name('admin.user.undelete');

            Route::get('user/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.user.edit');
            Route::post('user/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.user.update');

            Route::get('search/user', [\App\Http\Controllers\Admin\UserSearchController::class, 'show'])->name('user.search');

            Route::get('course/import', [\App\Http\Controllers\Admin\CourseImportController::class, 'show'])->name('course.import');
            Route::post('course/import', [\App\Http\Controllers\Admin\CourseImportController::class, 'store'])->name('course.import.store');

            Route::post('course/{course}/users', [\App\Http\Controllers\Admin\CourseUsersController::class, 'update'])->name('course.users.update');
            Route::post('courses/remove-staff', [\App\Http\Controllers\Admin\CourseUsersController::class, 'destroy'])->name('admin.courses.clear_staff');

            Route::post('course/{course}/disable', [\App\Http\Controllers\Admin\CourseStatusController::class, 'disable'])->name('course.disable');
            Route::post('course/{id}/enable', [\App\Http\Controllers\Admin\CourseStatusController::class, 'enable'])->name('course.enable');

            Route::get('course/{course}/edit', [\App\Http\Controllers\Admin\CourseController::class, 'edit'])->name('course.edit');
            Route::post('course/{course}', [\App\Http\Controllers\Admin\CourseController::class, 'update'])->name('course.update');


            Route::post('discipline/contacts', [\App\Http\Controllers\Admin\DisciplineContactController::class, 'update'])->name('discipline.contacts.update');

            Route::post('wlm/import', [\App\Http\Controllers\Admin\WlmImportController::class, 'update'])->name('wlm.import');
            Route::post('user/{user}/toggle-admin', [\App\Http\Controllers\Admin\AdminPermissionController::class, 'update'])->name('admin.toggle');
            Route::get('options', [\App\Http\Controllers\Admin\OptionsController::class, 'edit'])->name('admin.options.edit');
            Route::post('options', [\App\Http\Controllers\Admin\OptionsController::class, 'update'])->name('admin.options.update');

            Route::get('archives', [\App\Http\Controllers\Admin\ArchiveController::class, 'index'])->name('archive.index');
            Route::post('course/{course}/archive', [\App\Http\Controllers\Admin\ArchiveCourseController::class, 'store'])->name('course.papers.archive');
            Route::get('area/archive', [\App\Http\Controllers\Admin\ArchiveAreaController::class, 'show'])
                    ->name('area.papers.archive_form');
            Route::post('area/archive', [\App\Http\Controllers\Admin\ArchiveAreaController::class, 'store'])->name('area.papers.archive');

            Route::get('/notify/externals', [\App\Http\Controllers\Admin\NotifyExternalsController::class, 'show'])
                    ->name('admin.notify.externals.show');
            Route::post('notify/externals', [\App\Http\Controllers\Admin\NotifyExternalsController::class, 'store'])->name('admin.notify.externals');
            Route::post('notify/{course}/externals', [\App\Http\Controllers\Admin\NotifyExternalsController::class, 'course'])->name('admin.notify.externals_course');

            Route::post('/export/registry', [\App\Http\Controllers\Admin\ExportPapersForRegistryController::class, 'store'])->name('export.paper.registry');
            Route::get('/download/registry/{user}', [\App\Http\Controllers\Admin\DownloadPapersForRegistryController::class, 'show'])->name('download.papers.registry');

            Route::post('/export/checklists', [\App\Http\Controllers\Admin\ExportChecklistsController::class, 'store'])->name('checklist.bulk_download');
            Route::get('/download/checklists/{user}', [\App\Http\Controllers\Admin\DownloadChecklistsController::class, 'show'])->name('download.checklists');
        });
    });
});

Route::middleware([
    'api.token',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('/api')->group(function () {
    Route::get('/courses', [\App\Http\Controllers\Api\CourseController::class, 'index'])->name('api.course.index');
    Route::get('/course/{code}', [\App\Http\Controllers\Api\CourseController::class, 'show'])->name('api.course.show');
    Route::get('/course/{code}/staff', [\App\Http\Controllers\Api\CourseStaffController::class, 'show'])->name('api.course.staff');
    Route::get('/course/{code}/papers', [\App\Http\Controllers\Api\CoursePaperController::class, 'show'])->name('api.course.papers');
});
