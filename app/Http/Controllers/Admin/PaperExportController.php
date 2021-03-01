<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Http\Controllers\Controller;

class PaperExportController extends Controller
{
    public function show()
    {
        $courses = Course::with([
            'papers',
            'setters',
            'moderators',
            'checklists',
            'discipline'
        ])->orderBy('code')->get();

        $rows[] = [
            'Code',
            'Semester',
            'Title',
            'Discipline',
            'Setter Checklist',
            'Moderator Checklist',
            'Pre-internally Moderated',
            'Moderator Comments',
            'Post-internally Moderated',
            'External Comments',
            'Paper for Registry',
        ];

        foreach ($courses as $course) {
            foreach (['main', 'resit'] as $category) {
                $rows[] = [
                    $course->code,
                    $course->semester,
                    $course->title,
                    optional($course->discipline)->title,
                    $course->hasSetterChecklist($category) ? 'Y' : 'N',
                    $course->hasModeratorChecklist($category) ? 'Y' : 'N',
                    $course->datePaperAdded($category, \App\Paper::PRE_INTERNALLY_MODERATED),
                    $course->datePaperAdded($category, \App\Paper::MODERATOR_COMMENTS),
                    $course->datePaperAdded($category, \App\Paper::POST_INTERNALLY_MODERATED),
                    $course->datePaperAdded($category, \App\Paper::EXTERNAL_COMMENTS),
                    $course->datePaperAdded($category, \App\Paper::PAPER_FOR_REGISTRY),
                ];
            }
        }

        $filename = (new ExcelSheet)->generate($rows);

        return response()->download($filename, "examdb_papers_" . now()->format('d_m_Y_H_i') . '.xlsx');
    }
}
