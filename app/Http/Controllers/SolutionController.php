<?php

namespace App\Http\Controllers;

use App\Paper;
use App\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SolutionController extends Controller
{
    public function store(Course $course, Request $request)
    {
        $request->validate([
            'solution' => 'required|file',
            'category' => ['required', Rule::in(Paper::VALID_CATEGORIES)],
        ]);

        $paper = $course->addSolution($request->category, $request->file('solution'));

        if ($request->filled('comment_category')) {
            $paper->addComment($request->comment_category, $request->comment);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Solution added',
            ]);
        }

        return redirect()->route('course.show', $course);
    }
}
