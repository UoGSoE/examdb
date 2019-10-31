<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\Paper;

class PaperApprovalController extends Controller
{
    public function store(Course $course, string $category, Request $request)
    {
        $this->authorize('update', $course);

        $course->paperApprovedBy($request->user(), $category);

        $course->append('user_approved_main');
        $course->append('user_approved_resit');

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Paper Approved',
                'course' => $course,
            ]);
        }

        return redirect()->route('course.show', $course);
    }

    public function destroy(Course $course, string $category, Request $request)
    {
        $this->authorize('update', $course);

        $course->paperUnapprovedBy($request->user(), $category);

        $course->append('user_approved_main');
        $course->append('user_approved_resit');

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Paper Unapproved',
                'course' => $course,
            ]);
        }

        return redirect()->route('course.show', $course);
    }
}
