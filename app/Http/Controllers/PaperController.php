<?php

namespace App\Http\Controllers;

use App\Paper;
use App\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaperController extends Controller
{
    public function store(Course $course, Request $request)
    {
        $request->validate([
            'paper' => 'required|file',
            'category' => ['required', Rule::in(Paper::VALID_CATEGORIES)],
        ]);

        $paper = $course->addPaper($request->category, $request->file('paper'));

        if ($request->filled('comment_category')) {
            $paper->addComment($request->comment_category, $request->comment);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Paper added',
            ]);
        }

        return redirect()->route('course.show', $course);
    }
}
