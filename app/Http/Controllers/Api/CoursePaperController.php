<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaperResource;
use Illuminate\Http\Request;

class CoursePaperController extends Controller
{
    public function show(string $code)
    {
        $course = Course::where('code', '=', $code)->firstOrFail();

        return PaperResource::collection($course->papers);
    }
}
