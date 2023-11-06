<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseUsersController extends Controller
{
    public function update(Course $course, Request $request): JsonResponse
    {
        $request->validate([
            'setters' => 'present|array',
            'moderators' => 'present|array',
            'externals' => 'present|array',
        ]);

        $course->updateStaff($request->setters, $request->moderators, $request->externals);

        return response()->json([
            'message' => 'Updated',
        ], 200);
    }

    public function destroy(): RedirectResponse
    {
        Course::all()->each(function ($course) {
            $course->staff()->sync([]);
        });

        activity()
            ->causedBy(request()->user())
            ->log('Removed all staff from all courses');

        return redirect()->route('course.index');
    }
}
