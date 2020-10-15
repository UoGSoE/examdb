<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
