<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Jobs\ImportCourseDataBatch;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use Tests\Feature\ImportCourseDataSpreadsheetTest;

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
