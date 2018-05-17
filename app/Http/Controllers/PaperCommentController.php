<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Paper;

class PaperCommentController extends Controller
{
    public function store(Paper $paper, Request $request)
    {
        $request->validate([
            'category' => 'required',
            'comment' => 'required',
        ]);

        $paper->addComment($request->category, $request->comment);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Comment Added',
            ]);
        }

        return redirect()->route('course.show', $paper->course);
    }
}
