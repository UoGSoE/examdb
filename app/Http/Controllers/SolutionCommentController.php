<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Solution;

class SolutionCommentController extends Controller
{
    public function store(Solution $solution, Request $request)
    {
        $request->validate([
            'category' => 'required',
            'comment' => 'required',
        ]);

        $solution->addComment($request->category, $request->comment);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Comment Added',
            ]);
        }

        return redirect()->route('course.show', $solution->course);
    }
}
