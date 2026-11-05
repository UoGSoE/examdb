<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaperResource;
use App\Models\Course;

class CoursePaperController extends Controller
{
    public function show(string $code)
    {
        $course = Course::where('code', '=', $code)->firstOrFail();

        return PaperResource::collection($course->papers);
    }
}
