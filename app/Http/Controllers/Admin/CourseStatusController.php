<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseStatusController extends Controller
{
    public function disable(Course $course)
    {
        $course->disable();

        return response()->json([
            'message' => 'disabled'
        ]);
    }

    public function enable($id)
    {
        Course::withTrashed()->findOrFail($id)->enable();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'enabled'
            ]);
        }

        return redirect()->route('course.index', ['withtrashed' => 1]);
    }
}
