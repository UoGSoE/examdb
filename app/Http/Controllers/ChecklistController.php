<?php

namespace App\Http\Controllers;

use App\User;
use App\Course;
use App\Jobs\BulkExportChecklists;
use App\PaperChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ChecklistController extends Controller
{
    public function show(PaperChecklist $checklist)
    {
        // $this->authorize('show', $checklist->course);
        if (Route::currentRouteName() == 'api.course.checklist.show') {
            auth()->login(User::where('is_admin', '=', true)->first());
        }

        return view('course.checklist.show', [
            'checklist' => $checklist,
            'course' => $checklist->course,
            'category' => $checklist->category,
        ]);
    }

    public function create(Course $course)
    {
        $this->authorize('show', $course);

        $category = request('category');

        $checklist = $course->getNewChecklist($category);

        return view('course.checklist.create', [
            'course' => $course,
            'category' => $category,
            'checklist' => $checklist,
        ]);
    }

    public function test()
    {
        BulkExportChecklists::dispatch(User::first());
    }

    public function test2(PaperChecklist $checklist)
    {
        auth()->login(User::first());
        return view('pdf.checklist', [
            'checklist' => $checklist,
            'course' => $checklist->course,
            'category' => $checklist->category,
        ]);
    }
}
