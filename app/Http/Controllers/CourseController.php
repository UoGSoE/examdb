<?php

namespace App\Http\Controllers;

use App\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function show(Course $course)
    {
        $this->authorize('show', $course);

        return view('course.show', [
            'course' => $course,
        ]);
    }
}
