<?php

namespace App\Http\Controllers\Api;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
