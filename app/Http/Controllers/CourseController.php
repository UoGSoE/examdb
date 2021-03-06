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
        $course->load('papers');
        $course->append('is_uestc');
        $course->append('has_main_paper_for_registry');
        $course->append('has_resit_paper_for_registry');

        return view('course.show', [
            'course' => $course,
            'papers' => collect([
                'main' => $course->getMainPapers(),
                'resit' => $course->getResitPapers(),
                'resit2' => $course->getResit2Papers(),
            ]),
            'archivedPapers' => $course->archivedPapers()->withoutComments()->latest()->get(),
            'staff' => User::getStaffForVueSelect(),
            'externals' => User::getExternalsForVueSelect(),
        ]);
    }
}
