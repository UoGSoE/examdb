<?php

namespace App\Http\Controllers;

use App\Paper;
use App\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PaperController extends Controller
{
    public function store(Course $course, Request $request)
    {
        $request->validate([
            'paper' => 'required|file',
            'category' => 'required',
            'subcategory' => 'required',
        ]);

        $paper = $course->addPaper($request->category, $request->subcategory, $request->file('paper'));

        if ($request->filled('comment')) {
            $paper->addComment($request->comment);
        }

        if ($request->wantsJson()) {
            return response()->json($paper, 201);
        }

        return redirect()->route('course.show', $course);
    }

    public function show(Paper $paper)
    {
        return Storage::disk('exampapers')->download($paper->filename, $paper->original_filename);
    }
}
