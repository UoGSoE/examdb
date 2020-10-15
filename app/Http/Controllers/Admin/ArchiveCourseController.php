<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ArchiveCourseController extends Controller
{
    public function show(Course $course)
    {
        return view('admin.archive.course_form', [
            'course' => $course,
        ]);
    }

    public function store(Course $course, Request $request)
    {
        $course->papers->each->archive();
        $course->checklists->each->archive();
        $course->removeAllApprovals();

        activity()
            ->causedBy($request->user())
            ->log('Archived papers for '.ucfirst($course->code));

        return redirect()->route('course.show', $course->id);
    }
}
