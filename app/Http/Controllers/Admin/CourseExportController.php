<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Http\Controllers\Controller;

class CourseExportController extends Controller
{
    public function show()
    {
        $courses = Course::with([
            'setters',
            'moderators',
            'externals',
            'discipline',
            'checklists'
        ])->orderBy('code')->get();

        $rows[] = [
            'Code',
            'Title',
            'Discipline',
            'Semester',
            'Setters GUIDs',
            'Setters Names',
            'Moderators GUIDs',
            'Moderators Names',
            'Externals Emails',
            'Externals Names',
            'Examined?',
            'Main/Moderator',
            'Main/External',
            'Resit/Moderator',
            'Resit/External',
        ];
        foreach ($courses as $course) {
            $rows[] = [
                $course->code,
                $course->title,
                optional($course->discipline)->title,
                $course->semester,
                $course->setters->pluck('username')->implode(', '),
                $course->setters->pluck('full_name')->implode(', '),
                $course->moderators->pluck('username')->implode(', '),
                $course->moderators->pluck('full_name')->implode(', '),
                $course->externals->pluck('email')->implode(', '),
                $course->externals->pluck('full_name')->implode(', '),
                $course->isExamined() ? 'Y' : 'N',
                $course->isApprovedByModerator('main') ? 'Y' : 'N',
                $course->hasExternalChecklist('main') ? 'Y' : 'N',
                $course->isApprovedByModerator('resit') ? 'Y' : 'N',
                $course->hasExternalChecklist('resit') ? 'Y' : 'N',
            ];
        }

        $filename = (new ExcelSheet)->generate($rows);

        return response()->download($filename, "examdb_courses_" . now()->format('d_m_Y_H_i') . '.xlsx');
    }
}
