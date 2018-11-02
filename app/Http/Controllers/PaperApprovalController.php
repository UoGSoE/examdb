<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;

class PaperApprovalController extends Controller
{
    public function store(Course $course, string $category, Request $request)
    {
        $this->authorize('update', $course);

        $course->paperApprovedBy($request->user(), $category);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Paper Approved',
            ]);
        }

        return redirect()->route('course.show', $course);
    }

    public function destroy(Course $course, string $category, Request $request)
    {
        $this->authorize('update', $course);

        $course->paperUnapprovedBy($request->user(), $category);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Paper Unapproved',
            ]);
        }

        return redirect()->route('course.show', $course);
    }
}
