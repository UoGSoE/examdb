<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;

class PaperExportController extends Controller
{
    public function show()
    {
        $courses = Course::with([
            'papers',
            'setters',
            'moderators',
            'checklists',
            'discipline',
        ])->orderBy('code')->get();

        $rows[] = [
            'Code',
            'Semester',
            'Category',
            'Title',
            'Discipline',
            'Setter Checklist',
            'Moderator Checklist',
            'Pre-internally Moderated',
            'Moderator Comments',
            'Post-internally Moderated',
            'External Comments',
            'Paper for Registry',
            'Print Ready Paper',
            'Print Ready Approved?',
        ];

        foreach ($courses as $course) {
            foreach (['main', 'resit'] as $category) {
                $printReadyColumnText = '';
                if ($course->printReadyPaperRejected($category)) {
                    $printReadyColumnText = 'Rejected : '.$course->printReadyPaperRejectedMessage($category);
                } else {
                    $printReadyColumnText = $course->printReadyPaperApproved($category) ? 'Yes' : 'No';
                }

                $rows[] = [
                    $course->code,
                    $course->semester,
                    $category,
                    $course->title,
                    $course->discipline?->title,
                    $course->hasSetterChecklist($category) ? 'Y' : 'N',
                    $course->hasModeratorChecklist($category) ? 'Y' : 'N',
                    $course->datePaperAdded($category, \App\Models\Paper::PRE_INTERNALLY_MODERATED),
                    $course->dateModeratorFilledChecklist($category),
                    $course->datePaperAdded($category, \App\Models\Paper::POST_INTERNALLY_MODERATED),
                    $course->dateExternalFilledChecklist($category),
                    $course->datePaperAdded($category, \App\Models\Paper::PAPER_FOR_REGISTRY),
                    $course->datePaperAdded($category, \App\Models\Paper::ADMIN_PRINT_READY_VERSION),
                    $printReadyColumnText,
                ];
            }
        }

        $filename = (new ExcelSheet)->generate($rows);

        return response()->download($filename, 'examdb_papers_'.now()->format('d_m_Y_H_i').'.xlsx');
    }
}
