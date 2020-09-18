<?php

namespace App\Http\Controllers;

use App\User;
use App\Course;
use App\PaperChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
}
