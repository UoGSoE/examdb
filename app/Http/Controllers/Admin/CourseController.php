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

        return view('admin.courses.index');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', [
            'course' => $course,
            'disciplines' => Discipline::orderBy('title')->get(),
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
            'discipline_id' => 'required|integer',
            'is_examined' => 'required|boolean',
        ]);

        $course->update($request->only(['code', 'title', 'discipline_id', 'is_examined']));

        return redirect(route('course.show', $course->id));
    }
}
