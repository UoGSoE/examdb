<?php

namespace App\Http\Controllers;

use App\Course;
use App\Paper;
use Illuminate\Http\Request;

class PaperApprovalController extends Controller
{
    public function store(Course $course, string $category, Request $request)
    {
        $this->authorize('approve', $course);

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
        $this->authorize('approve', $course);

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
