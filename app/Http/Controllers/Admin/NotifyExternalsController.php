<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyExternals;
use App\Mail\NotifyExternalSpecificCourse;
use App\Models\Course;
use App\Models\Discipline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotifyExternalsController extends Controller
{
    public function show()
    {
        return view('admin.email_externals', [
            'disciplines' => Discipline::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $disciplines = Discipline::orderBy('title')->get()->pluck('title')->implode(',');
        $request->validate([
            'area' => 'required|in:'.$disciplines,
        ]);

        NotifyExternals::dispatch($request->area);

        activity()
            ->causedBy($request->user())
            ->log('Notified externals for '.ucfirst($request->area));

        if ($request->wantsJson()) {
            return response()->json([], 200);
        }

        return redirect()->route('paper.index');
    }

    public function course(Course $course)
    {
        $course->externals->each(function ($external) use ($course) {
            Mail::to($external->email)->queue(new NotifyExternalSpecificCourse($course));
        });

        $course->markExternalNotified();

        activity()
            ->causedBy(request()->user())
            ->log('Notified externals for '.$course->code);
    }
}
