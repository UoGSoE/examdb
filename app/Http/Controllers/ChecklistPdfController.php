<?php

namespace App\Http\Controllers;

use App\PaperChecklist;
use Illuminate\Http\Request;

class ChecklistPdfController extends Controller
{
    public function show(PaperChecklist $checklist)
    {
        $this->authorize('show', $checklist->course);

        $pdf = \PDF::loadView('course.checklist.pdf', ['checklist' => $checklist]);
        return $pdf->download($checklist->course->code . '_paper_checklist.pdf');
    }
}
