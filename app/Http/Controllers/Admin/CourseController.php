<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use App\Discipline;
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
        if (request()->discipline) {
            $query = $query->where('discipline_id', '=', request()->discipline);
        }

        return view('admin.courses.index', [
            'courses' => $query->get(),
            'disciplines' => Discipline::orderBy('title')->get(),
            'disciplineFilter' => request()->discipline,
        ]);
    }
}
