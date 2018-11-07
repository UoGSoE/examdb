<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function index()
    {
        return view('admin.courses.index', [
            'courses' => Course::with(['setters', 'moderators', 'externals'])->orderBy('code')->get(),
        ]);
    }
}
