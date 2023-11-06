<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Course;
use Illuminate\Http\Request;

class PaperForRegistryApprovalController extends Controller
{
    public function approve(Course $course, Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|in:main,resit',
        ]);

        $course->approvePaperForRegistry($request->category);

        activity()->causedBy($request->user)->performedOn($course)->log('Approved '.$request->category.' paper for registry ('.$course->code.')');

        return response()->json([
            'message' => 'approved',
        ]);
    }
}
