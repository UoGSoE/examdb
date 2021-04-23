<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use App\Http\Controllers\Controller;
use App\Paper;
use Illuminate\Http\Request;

class ArchiveAreaController extends Controller
{
    public function show()
    {
        return view('admin.archive.form');
    }

    public function store(Request $request)
    {
        Course::with('papers')->get()->each(function ($course) {
            $course->papers->each->archive();
            $course->checklists->each->archive();
            $course->removeAllApprovals();
        });

        activity()
            ->causedBy($request->user())
            ->log('Archived papers');

        return redirect()->route('paper.index');
    }
}
