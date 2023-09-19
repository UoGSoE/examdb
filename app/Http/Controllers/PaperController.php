<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Events\PaperAdded;
use App\Models\Paper;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaperController extends Controller
{
    public function index(int $id)
    {
        $course = Course::findOrFail($id);

        $this->authorize('show', $course);

        $allSessionCourses = Course::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('code', '=', $course->code)->get();
        $papers = Paper::withoutGlobalScope(CurrentAcademicSessionScope::class)
                    ->with([
                        'user' => fn ($query) => $query->withoutGlobalScope(CurrentAcademicSessionScope::class),
                        'comments',
                    ])
                    ->whereIn('course_id', $allSessionCourses->pluck('id')->values())
                    ->orderByDesc('created_at')
                    ->get();

        return view('course.all.index', [
            'course' => $course,
            'papers' => $papers,
        ]);
    }

    public function store(Course $course, Request $request)
    {
        Gate::authorize('upload_paper', $course);

        $request->validate([
            'paper' => 'required|file|max:20000',
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

    public function show(int $paperId)
    {
        // in order to check if the user can view this paper, we need to find the associated course in the
        // _current_ academic session to see if they are the setter/moderator/external for it.
        // then we can check if they can view the paper from past 'versions' of the course in the
        // PaperPolicy::view() method which they might not have had anything to do with.
        $paper = Paper::withoutGlobalScope(CurrentAcademicSessionScope::class)
                    ->with(['course' => fn ($query) => $query->withoutGlobalScope(CurrentAcademicSessionScope::class)])
                    ->findOrFail($paperId);
        $currentVersionOfCourse = Course::where('code', '=', $paper->course->code)->firstOrFail();
        $paper->setRelation('course', $currentVersionOfCourse);

        $this->authorize('view', $paper);

        $encryptedContent = Storage::disk('exampapers')->get($paper->filename);
        $decryptedContent = decrypt($encryptedContent);

        activity()
            ->causedBy(request()->user())
            ->log(
                "Downloaded {$paper->category} paper '{$paper->original_filename}' for {$paper->course->code}"
            );

        return response()->streamDownload(function () use ($decryptedContent) {
            echo $decryptedContent;
        }, $paper->original_filename, ['Content-Type', $paper->mimetype]);
    }

    public function destroy(Paper $paper)
    {
        $this->authorize('delete', $paper);

        $course = $paper->course;

        // Just hide the paper if the user is an admin, in case of a mistake
        if (Auth::user()->isAdmin()) {
            $paper->is_hidden = true;
            $paper->save();

            activity()
                ->causedBy(request()->user())
                ->log(
                    "Admin deleted {$paper->category} paper '{$paper->original_filename}' for {$paper->course->code}"
                );

            return response()->json([
                'papers' => collect([
                    'main' => $course->mainPapers()->with(['user', 'comments'])->latest()->get(),
                    'resit' => $course->resitPapers()->with(['user', 'comments'])->latest()->get(),
                    'resit2' => $course->resit2Papers()->with(['user', 'comments'])->latest()->get(),
                ]),
            ]);
        }

        Storage::disk('exampapers')->delete($paper->filename);
        $paper->delete();

        activity()
            ->causedBy(request()->user())
            ->log(
                "Deleted {$paper->category} paper '{$paper->original_filename}' for {$paper->course->code}"
            );

        return response()->json([
            'papers' => collect([
                'main' => $course->mainPapers()->with(['user', 'comments'])->latest()->get(),
                'resit' => $course->resitPapers()->with(['user', 'comments'])->latest()->get(),
                'resit2' => $course->resit2Papers()->with(['user', 'comments'])->latest()->get(),
            ]),
        ]);
    }
}
