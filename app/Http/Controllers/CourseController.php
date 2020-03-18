<?php

namespace App\Http\Controllers;

use App\Course;
use App\User;
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
        $course->append('is_uestc');

        return view('course.show', [
            'course' => $course,
            'papers' => collect([
                'main' => $course->mainPapers()->with(['user', 'comments'])->latest()->get(),
                'resit' => $course->resitPapers()->with(['user', 'comments'])->latest()->get(),
                'resit2' => $course->resit2Papers()->with(['user', 'comments'])->latest()->get(),
            ]),
            'archivedPapers' => $course->archivedPapers()->withoutComments()->latest()->get(),
            'staff' => User::getStaffForVueSelect(),
            'externals' => User::getExternalsForVueSelect(),
        ]);
    }
}
