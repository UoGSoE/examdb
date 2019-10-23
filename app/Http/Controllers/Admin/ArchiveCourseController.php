<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        activity()
            ->causedBy($request->user())
            ->log("Archived papers for " . ucfirst($course->code));


        return redirect()->route('course.show', $course->id);
    }
}
