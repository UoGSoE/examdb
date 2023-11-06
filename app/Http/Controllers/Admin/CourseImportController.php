<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportCourseDataBatch;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;

class CourseImportController extends Controller
{
    public function show()
    {
        return view('admin.courses.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->import($request->file('sheet')->getPathname());
        ImportCourseDataBatch::dispatch($data, $request->user()->id, $request->user()->getCurrentAcademicSession()->id);

        return redirect(route('course.index'))->with('success', 'Import started - you will get an email when completed');
    }
}
