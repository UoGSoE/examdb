<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function index()
    {
        $query = Course::with(['setters', 'moderators', 'externals', 'discipline'])->orderBy('code');
        if (request()->withtrashed) {
            $query = $query->withTrashed();
        }

        return view('admin.courses.index', [
            'courses' => $query->get(),
        ]);
    }
}
