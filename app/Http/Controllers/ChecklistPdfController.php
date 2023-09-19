<?php

namespace App\Http\Controllers;

use App\Models\PaperChecklist;
use Illuminate\Http\Request;

class ChecklistPdfController extends Controller
{
    public function show(PaperChecklist $checklist)
    {
        $this->authorize('show', $checklist->course);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('course.checklist.show', [
            'checklist' => $checklist,
            'course' => $checklist->course,
            'category' => $checklist->category,
        ]);

        return $pdf->download($checklist->course->code.'_paper_checklist.pdf');
    }
}
