<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Jobs\NotifyExternals;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalHasPapersToLookAt;
use App\Mail\NotifyExternalSpecificCourse;

class NotifyExternalsController extends Controller
{
    public function show()
    {
        return view('admin.email_externals');
    }

    public function store(Request $request)
    {
        $request->validate([
            'area' => 'required|in:glasgow,uestc'
        ]);

        NotifyExternals::dispatch($request->area);

        activity()
            ->causedBy($request->user())
            ->log("Notified externals for " . ucfirst($request->area));

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
    }
}
