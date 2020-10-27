<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use App\Discipline;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function edit(Course $course)
    {
        return view('admin.courses.edit', [
            'course' => $course,
        ]);
    }

    public function update(Course $course, Request $request)
    {
        $request->validate([
            'code' => [
                'required',
                Rule::unique('courses')->ignore($course->id),
            ],
            'title' => 'required',
        ]);

        $course->update($request->only(['code', 'title']));

        return redirect(route('course.show', $course->id));
    }
}
