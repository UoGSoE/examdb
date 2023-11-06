<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\PaperChecklist;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ChecklistController extends Controller
{
    public function show(PaperChecklist $checklist): View
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

    public function create(Course $course): View
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

    public function showForPdfPrinter(PaperChecklist $checklist): View
    {
        auth()->login(User::first());

        return view('pdf.checklist', [
            'checklist' => $checklist,
            'course' => $checklist->course,
            'category' => $checklist->category,
        ]);
    }
}
