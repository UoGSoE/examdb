<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use Box\Spout\Common\Entity\Row;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Http\Controllers\Controller;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

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
            'Semester',
            'Title',
            'Discipline',
            'Main/Moderator',
            'Main/External',
            'Resit/Moderator',
            'Resit/External',
            'Setters',
            'Moderators',
            'Externals'
        ];
        foreach ($courses as $course) {
            $rows[] = [
                $course->code,
                $course->semester,
                $course->title,
                optional($course->discipline)->title,
                $course->isApprovedByModerator('main') ? 'Y' : 'N',
                $course->hasExternalChecklist('main') ? 'Y' : 'N',
                $course->isApprovedByModerator('resit') ? 'Y' : 'N',
                $course->hasExternalChecklist('resit') ? 'Y' : 'N',
                $course->setters->pluck('full_name')->implode(', '),
                $course->moderators->pluck('full_name')->implode(', '),
                $course->externals->pluck('full_name')->implode(', '),
            ];
        }

        $filename = (new ExcelSheet)->generate($rows);

        return response()->download($filename, "examdb_courses_" . now()->format('d_m_Y_H_i') . '.xlsx');
    }
}
