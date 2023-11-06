<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseStaffController extends Controller
{
    public function show($code)
    {
        $course = Course::where('code', '=', $code)->firstOrFail();
        $course->load('setters');
        $course->load('moderators');
        $course->load('externals');

        return response()->json([
            'course' => $course->toArray(),
        ]);
    }
}
