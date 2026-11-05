<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        return CourseResource::collection(Course::orderBy('code')->get());
    }

    public function show(string $code)
    {
        $course = Course::where('code', '=', $code)->firstOrFail();

        return new CourseResource($course);
    }
}
