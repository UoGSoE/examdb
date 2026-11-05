<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Ohffs\SimpleSpout\ExcelSheet;

class CourseExportController extends Controller
{
    public function show()
    {
        $courses = Course::with([
            'setters',
            'moderators',
            'externals',
            'discipline',
            'checklists',
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
            'Main/Setter',
            'Main/Moderator',
            'Main/External',
            'Resit/Setter',
            'Resit/Moderator',
            'Resit/External',
        ];
        foreach ($courses as $course) {
            $rows[] = [
                $course->code,
                $course->title,
                $course->discipline?->title,
                $course->semester,
                $course->setters->pluck('username')->implode(', '),
                $course->setters->pluck('full_name')->implode(', '),
                $course->moderators->pluck('username')->implode(', '),
                $course->moderators->pluck('full_name')->implode(', '),
                $course->externals->pluck('email')->implode(', '),
                $course->externals->pluck('full_name')->implode(', '),
                $course->isExamined() ? 'Y' : 'N',
                $course->hasSetterChecklist('main') ? 'Y' : 'N',
                $course->isApprovedByModerator('main') ? 'Y' : 'N',
                $course->hasExternalChecklist('main') ? 'Y' : 'N',
                $course->hasSetterChecklist('resit') ? 'Y' : 'N',
                $course->isApprovedByModerator('resit') ? 'Y' : 'N',
                $course->hasExternalChecklist('resit') ? 'Y' : 'N',
            ];
        }

        $filename = (new ExcelSheet)->generate($rows);

        return response()->download($filename, 'examdb_courses_'.now()->format('d_m_Y_H_i').'.xlsx');
    }
}
