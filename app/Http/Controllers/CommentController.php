<?php

namespace App\Http\Controllers;

use App\Events\PaperAdded;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class CommentController extends Controller
{
    #[Authorize('upload_paper', 'course')]
    public function store(Course $course, Request $request)
    {
        $request->validate([
            'category' => 'required',
            'comment' => 'required',
        ]);

        // here we create a fake file so the rest of the code that _requires_ a file to associate
        // with the Paper can still work as-is.  See gitlab issue #61
        $fakePaper = UploadedFile::fake()->create('paper_comment', 1);
        $paper = $course->addPaper($request->category, 'comment', $fakePaper);

        if ($request->filled('comment')) {
            $paper->addComment($request->comment);
        }

        event(new PaperAdded($paper, $request->user()));

        $paper->load('user');
        $paper->load('comments');

        if ($request->wantsJson()) {
            return response()->json($paper, 201);
        }

        return redirect()->route('course.show', $course);
    }
}
