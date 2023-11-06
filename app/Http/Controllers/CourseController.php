<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Course $course): View
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
        $course->has_main_checklist = $course->hasPreviousChecklists('main') || $course->hasPreviousChecklists('assessment');
        $course->has_resit_checklist = $course->hasPreviousChecklists('resit');
        $course->has_resit2_checklist = $course->hasPreviousChecklists('resit2');

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
