<?php

namespace App\Http\Controllers;

use App\Course;
use Illuminate\Http\Request;

class PaperForRegistryApprovalController extends Controller
{
    public function approve(Course $course, Request $request)
    {
        $request->validate([
            'category' => 'required|in:main,resit',
        ]);

        $course->approvePaperForRegistry($request->category);

        activity()->causedBy($request->user)->performedOn($course)->log('Approved ' . $request->category . ' paper for registry (' . $course->code . ')');

        return response()->json([
            'message' => 'approved',
        ]);
    }
}
