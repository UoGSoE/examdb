<?php

namespace App\Http\Controllers;

use App\Paper;
use App\Course;
use App\Events\PaperAdded;
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

        event(new PaperAdded($paper, $request->user()));

        $paper->load('user');
        $paper->load('comments');

        if ($request->wantsJson()) {
            return response()->json($paper, 201);
        }

        return redirect()->route('course.show', $course);
    }

    public function show(Paper $paper)
    {
        $encryptedContent = Storage::disk('exampapers')->get($paper->filename);
        $decryptedContent = decrypt($encryptedContent);

        return response()->streamDownload(function () use ($decryptedContent) {
            echo $decryptedContent;
        }, $paper->original_filename, ['Content-Type', $paper->mimetype]);
        //return Storage::disk('exampapers')->download($paper->filename, $paper->original_filename);
    }

    public function destroy(Paper $paper)
    {
        $this->authorize('delete', $paper);

        $course = $paper->course;

        Storage::disk('exampapers')->delete($paper->filename);
        $paper->delete();

        return response()->json([
            'papers' => collect([
                'main' => $course->mainPapers()->with(['user', 'comments'])->latest()->get(),
                'resit' => $course->resitPapers()->with(['user', 'comments'])->latest()->get(),
            ]),
        ]);
    }
}
