<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

class CourseStatusController extends Controller
{
    public function disable(Course $course): JsonResponse
    {
        $course->disable();

        activity()->causedBy(request()->user())->log(
            'Disabled course '.$course->code
        );

        return response()->json([
            'message' => 'disabled',
        ]);
    }

    public function enable($id)
    {
        $course = Course::withTrashed()->findOrFail($id);
        $course->enable();

        activity()->causedBy(request()->user())->log(
            'Enabled course '.$course->code
        );

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'enabled',
            ]);
        }

        return redirect()->route('course.index', ['withtrashed' => 1]);
    }
}
