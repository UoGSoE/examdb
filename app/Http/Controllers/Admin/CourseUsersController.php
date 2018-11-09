<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

class CourseUsersController extends Controller
{
    public function update(Course $course, Request $request)
    {
        $request->validate([
            'setters' => 'present|array',
            'moderators' => 'present|array',
            'externals' => 'present|array',
        ]);

        $course->setters()->detach();
        User::findMany($request->setters)->each->markAsSetter($course);

        $course->moderators()->detach();
        User::findMany($request->moderators)->each->markAsModerator($course);

        $course->externals()->detach();
        User::findMany($request->externals)->each->markAsExternal($course);

        return response()->json([
            'message' => 'Updated'
        ], 200);
    }
}
