<?php

namespace App\Http\Controllers\Api;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaperResource;

class CoursePaperController extends Controller
{
    public function show(string $code)
    {
        $course = Course::where('code', '=', $code)->firstOrFail();

        return PaperResource::collection($course->papers);
    }
}
