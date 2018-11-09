<?php

namespace App\Http\Controllers;

use App\User;
use App\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function show(Course $course)
    {
        $this->authorize('show', $course);

        $course->append('user_approved_main');
        $course->append('user_approved_resit');
        $course->load('setters');
        $course->load('moderators');
        $course->load('externals');

        return view('course.show', [
            'course' => $course,
            'papers' => collect([
                'main' => $course->mainPapers()->with(['user', 'comments'])->latest()->get(),
                'resit' => $course->resitPapers()->with(['user', 'comments'])->latest()->get(),
            ]),
            'staff' => User::getStaffForVueSelect(),
            'externals' => User::getExternalsForVueSelect(),
        ]);
    }
}
