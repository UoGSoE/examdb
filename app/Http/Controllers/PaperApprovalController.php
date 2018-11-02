<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;

class PaperApprovalController extends Controller
{
    public function store(Course $course, string $category, Request $request)
    {
        $course->paperApprovedBy($request->user(), $category);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Paper Approved',
            ]);
        }

        return redirect()->route('course.show', $paper->course_id);
    }

    public function destroy(Paper $paper)
    {
        $paper->setterUnapproves();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Paper Unapproved',
            ]);
        }

        return redirect()->route('course.show', $paper->course_id);
    }
}
