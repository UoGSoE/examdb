<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discipline;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('admin.courses.index');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', [
            'course' => $course,
            'disciplines' => Discipline::orderBy('title')->get(),
        ]);
    }

    public function update(Course $course, Request $request): RedirectResponse
    {
        $request->validate([
            'code' => [
                'required',
            ],
            'title' => 'required',
            'discipline_id' => 'required|integer',
            'is_examined' => 'required|boolean',
            'semester' => 'required|integer|min:1|max:3',
        ]);

        $existingCourse = Course::withoutGlobalScope(CurrentAcademicSessionScope::class)
            ->where('code', '=', $request->code)
            ->where('academic_session_id', '=', $request->user()->getCurrentAcademicSession()->id)
            ->first();
        if ($existingCourse && $existingCourse->id != $course->id) {
            $error = ValidationException::withMessages([
                'code' => ['Course with this code already exists.'],
            ]);
            throw $error;
        }

        $course->update($request->only(['code', 'title', 'discipline_id', 'is_examined', 'semester']));

        return redirect(route('course.show', $course->id));
    }
}
