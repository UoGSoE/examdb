<?php

namespace App\Http\Controllers;

use App\Paper;
use App\Course;
use App\PaperChecklist;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Events\ChecklistUpdated;

class ChecklistController extends Controller
{
    public function show(PaperChecklist $checklist)
    {
        if (!$checklist->getNextChecklist()) {
            return redirect()->route('course.checklist.create', ['course' => $checklist->course, 'category' => $checklist->category]);
        }
        return view('course.checklist.show', [
            'checklist' => $checklist,
        ]);
    }

    public function create(Course $course)
    {
        $this->authorize('show', $course);

        $category = request('category');

        $checklist = PaperChecklist::where('course_id', '=', $course->id)
                        ->where('category', '=', $category)
                        ->latest()
                        ->first();
        if (!$checklist) {
            $checklist = new PaperChecklist([
                'course_id' => $course->id,
                'category' => $category,
                'version' => PaperChecklist::CURRENT_VERSION,
            ]);
        }

        return view('course.checklist.create', [
            'course' => $course,
            'category' => $category,
            'checklist' => $checklist,
        ]);
    }

    public function store(Course $course, Request $request)
    {
        $this->authorize('update', $course);

        $request->validate([
            'category' => ['required', Rule::in(Paper::VALID_CATEGORIES)],
            'q1' => 'nullable',
            'q2' => 'nullable',
        ]);

        $checklist = PaperChecklist::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'category' => $request->category,
            'version' => request('version', PaperChecklist::CURRENT_VERSION),
            'q1' => $request->q1,
            'q2' => $request->q2,
        ]);

        event(new ChecklistUpdated($checklist));

        return redirect()->route('course.show', $course->id);
    }
}
