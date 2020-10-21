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

        return response()->json([
            'message' => 'approved',
        ]);
    }
}
